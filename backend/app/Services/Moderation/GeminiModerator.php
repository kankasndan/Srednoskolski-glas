<?php

namespace App\Services\Moderation;

use App\Contracts\ContentModerator;
use App\Support\Moderation\ModerationDecision;
use App\Support\Moderation\ModerationVerdict;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Screens uploads with the Gemini API.
 *
 * Talks to the REST API directly (no SDK dependency), same as the ImageKit
 * driver. Still images small enough to fit under the payload cap are sent
 * inline. Videos are screened from a few JPEG stills (the same fast path as
 * photos); only PDFs and unreadable clips go through the Files API.
 *
 * @see https://ai.google.dev/gemini-api/docs/document-processing
 */
class GeminiModerator implements ContentModerator
{
    /**
     * Finish reasons that mean Gemini withheld its answer on safety grounds.
     *
     * PROHIBITED_CONTENT is a non-configurable filter that Google documents as
     * firing on CSAM, so a block here is the strongest signal the API can give
     * us — not an error.
     */
    private const BLOCKED_FINISH_REASONS = [
        'SAFETY',
        'PROHIBITED_CONTENT',
        'SPII',
        'BLOCKLIST',
        'IMAGE_SAFETY',
        'IMAGE_PROHIBITED_CONTENT',
    ];

    /**
     * The policy handed to the model on every call.
     *
     * The allow list is as explicit as the deny list on purpose: given only a
     * deny list the model refuses ordinary school, sport and beach photos.
     */
    private const POLICY = <<<'POLICY'
        You are a content moderator for an online forum used by Macedonian high-school
        students aged roughly 14 to 19. Decide whether the attached file may be published.

        Answer "reject" when the file contains any of:
        - nudity, sexual activity, sexual display, or pornography
        - sexualised depictions of a person, including implied or partially covered
        - graphic violence, gore, or detailed injury
        - promotion of self-harm, suicide, or eating disorders
        - hate symbols, or content attacking people over a protected characteristic
        - depictions of illegal drug use, weapons used as a threat, or sexual services

        Answer "escalate" when the file may sexualise, sexually expose, or exploit a
        person who could be under 18. Answer "escalate" whenever you are uncertain on
        this specific point: never answer "allow" or "reject" for content you suspect
        could be child sexual content.

        Answer "allow" for ordinary teenage life, including:
        - swimwear or beachwear in a non-sexual context, sport, dance, and gym photos
        - school, classroom, exam, homework and study material, screenshots, documents
        - memes, jokes, drawings, video games, food, pets, travel, and landscapes
        - anatomy or injury in a clearly educational or medical context
        - nudity in a clearly classical, art-historical, or fine-art context

        Judge only what is actually depicted, never what could be imagined around it. Be
        strict about the reject list and generous about the allow list: wrongly refusing a
        student's holiday photo has a real cost.

        Write "reason" as one short English sentence naming what you detected. It is
        recorded in moderation logs and is never shown to the uploader.
        POLICY;

    /**
     * @var array<string, mixed>
     */
    private const RESPONSE_SCHEMA = [
        'type' => 'OBJECT',
        'properties' => [
            'decision' => [
                'type' => 'STRING',
                'enum' => ['allow', 'reject', 'escalate'],
            ],
            'categories' => [
                'type' => 'ARRAY',
                'items' => ['type' => 'STRING'],
            ],
            'reason' => ['type' => 'STRING'],
        ],
        'required' => ['decision', 'reason'],
    ];

    /**
     * Schema for the student-text prompt (safe / flag / unsure).
     *
     * @var array<string, mixed>
     */
    private const TEXT_RESPONSE_SCHEMA = [
        'type' => 'OBJECT',
        'properties' => [
            'verdict' => [
                'type' => 'STRING',
                'enum' => ['safe', 'flag', 'unsure'],
            ],
            'confidence' => ['type' => 'NUMBER'],
            'categories' => [
                'type' => 'ARRAY',
                'items' => [
                    'type' => 'STRING',
                    'enum' => [
                        'profanity',
                        'sexual',
                        'nudity',
                        'hate',
                        'bullying',
                        'threats',
                        'self_harm',
                        'drugs',
                        'dangerous',
                        'privacy',
                        'spam',
                    ],
                ],
            ],
            'reason' => ['type' => 'STRING'],
        ],
        'required' => ['verdict', 'confidence', 'categories'],
    ];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly VideoFrameSampler $frames,
    ) {
        if (empty($this->config['api_key'])) {
            throw new RuntimeException('Missing Gemini configuration value [api_key]. Set GEMINI_API_KEY in your .env file.');
        }
    }

    public function review(UploadedFile $file): ModerationVerdict
    {
        if (! $file->isValid()) {
            throw new RuntimeException($file->getErrorMessage());
        }

        if ($this->isVideo($file)) {
            $stills = $this->frames->sample($file);

            if ($stills !== []) {
                return $this->interpret($this->generate(
                    $this->frameParts($stills),
                    $this->timeoutFor(),
                ));
            }

            Log::channel((string) config('moderation.log_channel', 'moderation'))
                ->notice('content_moderation.video_full_file', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
        }

        $timeout = $this->timeoutFor(fullVideoFile: $this->isVideo($file));

        if ($this->isVideo($file)) {
            // Windows counts sleep() toward max_execution_time, and Gemini video
            // analysis plus the later ImageKit upload easily exceeds 30 seconds.
            set_time_limit(max(180, $timeout + 60));
        }

        [$parts, $remoteFile] = $this->buildFilePart($file, $timeout);

        try {
            return $this->interpret($this->generate($parts, $timeout));
        } finally {
            // Never leave the file sitting in Google's temporary storage, whatever
            // the verdict was or whichever exception got us here.
            if ($remoteFile !== null) {
                $this->deleteRemoteFile($remoteFile, $timeout);
            }
        }
    }

    public function reviewText(string $text): ModerationVerdict
    {
        $text = trim($text);

        if ($text === '') {
            return ModerationVerdict::allow('empty text');
        }

        return $this->interpret($this->generate(
            [['text' => $text]],
            $this->timeoutFor(),
            self::textPolicy(),
            self::TEXT_RESPONSE_SCHEMA,
        ));
    }

    /**
     * @param  list<string>  $jpegs
     * @return list<array<string, mixed>>
     */
    private function frameParts(array $jpegs): array
    {
        $parts = [[
            'text' => 'These JPEG stills were sampled from an uploaded video. Review every frame. If any frame is disallowed, the video is disallowed.',
        ]];

        foreach ($jpegs as $jpeg) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => base64_encode($jpeg),
                ],
            ];
        }

        return $parts;
    }

    /**
     * Build the content parts for the file, uploading it first when it is too
     * large to inline.
     *
     * @return array{0: list<array<string, mixed>>, 1: string|null} The parts, and the remote file name to clean up.
     */
    private function buildFilePart(UploadedFile $file, int $timeout): array
    {
        $mimeType = (string) $file->getMimeType();

        if (! $this->isVideo($file) && (int) $file->getSize() <= $this->inlineMaxBytes()) {
            return [[[
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data' => base64_encode($file->getContent()),
                ],
            ]], null];
        }

        ['name' => $name, 'uri' => $uri] = $this->uploadFile($file, $timeout);

        $this->awaitProcessed($name, $timeout);

        return [[[
            'fileData' => [
                'mimeType' => $mimeType,
                'fileUri' => $uri,
            ],
        ]], $name];
    }

    /**
     * @param  list<array<string, mixed>>  $parts
     * @param  array<string, mixed>|null  $schema
     * @return array<string, mixed>
     */
    private function generate(array $parts, int $timeout, ?string $policy = null, ?array $schema = null): array
    {
        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $policy ?? self::POLICY]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => $parts,
            ]],
            'generationConfig' => [
                // A moderation gate that answers differently on identical input is
                // a support problem, so leave no room for sampling.
                'temperature' => 0,
                'responseMimeType' => 'application/json',
                'responseSchema' => $schema ?? self::RESPONSE_SCHEMA,
            ],
            // Without this the configurable filters refuse to classify borderline
            // files at all, which would reach us as a failure instead of a verdict
            // and make explicit content indistinguishable from prohibited content.
            'safetySettings' => $this->safetySettings(),
        ];

        $response = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = $this->request($timeout)
                ->retry(2, 250, function (Throwable $exception): bool {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    return $exception instanceof RequestException
                        && $exception->response->serverError();
                }, throw: false)
                ->post($this->modelUrl(), $payload);

            // throw: false means 503 is a returned response, not an exception,
            // so Laravel's retry() never sees it. Wait and try again.
            if ($response->status() !== 503) {
                break;
            }

            if ($attempt < 2 && ! app()->runningUnitTests()) {
                sleep($attempt + 1);
            }
        }

        if ($response->failed()) {
            throw new RuntimeException('Gemini moderation request failed: '.$response->body());
        }

        return (array) $response->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function interpret(array $payload): ModerationVerdict
    {
        // A blocked prompt means Gemini declined to look at the file at all. It
        // returns no candidate, so this has to be handled before parsing JSON.
        $blockReason = data_get($payload, 'promptFeedback.blockReason');

        if (is_string($blockReason) && $blockReason !== '') {
            return ModerationVerdict::escalate(
                "Gemini refused the input ({$blockReason}).",
                [$blockReason],
            );
        }

        $finishReason = data_get($payload, 'candidates.0.finishReason');

        if (is_string($finishReason) && in_array($finishReason, self::BLOCKED_FINISH_REASONS, true)) {
            return ModerationVerdict::escalate(
                "Gemini withheld its answer ({$finishReason}).",
                [$finishReason],
            );
        }

        $text = data_get($payload, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned no moderation verdict.');
        }

        $verdict = json_decode($text, true);

        if (! is_array($verdict)) {
            throw new RuntimeException('Gemini returned a malformed moderation verdict: '.$text);
        }

        if (array_key_exists('verdict', $verdict)) {
            return $this->fromTextVerdict($verdict, $text);
        }

        $decision = ModerationDecision::tryFrom((string) ($verdict['decision'] ?? ''));

        if ($decision === null) {
            throw new RuntimeException('Gemini returned an unknown moderation decision: '.$text);
        }

        return new ModerationVerdict(
            decision: $decision,
            reason: is_string($verdict['reason'] ?? null) ? $verdict['reason'] : null,
            categories: array_values(array_filter(
                (array) ($verdict['categories'] ?? []),
                static fn ($category): bool => is_string($category) && $category !== '',
            )),
        );
    }

    /**
     * Map the student-text prompt (safe / flag / unsure) onto the shared
     * allow / reject / escalate decisions the rest of the app already uses.
     *
     * @param  array<string, mixed>  $verdict
     */
    private function fromTextVerdict(array $verdict, string $raw): ModerationVerdict
    {
        $label = (string) ($verdict['verdict'] ?? '');
        $categories = array_values(array_filter(
            (array) ($verdict['categories'] ?? []),
            static fn ($category): bool => is_string($category) && $category !== '',
        ));
        $reason = is_string($verdict['reason'] ?? null) && trim($verdict['reason']) !== ''
            ? trim($verdict['reason'])
            : null;

        $decision = match ($label) {
            'safe' => ModerationDecision::Allow,
            'flag' => in_array('self_harm', $categories, true)
                ? ModerationDecision::Escalate
                : ModerationDecision::Reject,
            'unsure' => ModerationDecision::Escalate,
            default => null,
        };

        if ($decision === null) {
            throw new RuntimeException('Gemini returned an unknown text moderation verdict: '.$raw);
        }

        return new ModerationVerdict(
            decision: $decision,
            reason: $reason,
            categories: $categories,
        );
    }

    private static function textPolicy(): string
    {
        $path = __DIR__.DIRECTORY_SEPARATOR.'text-moderation-prompt.txt';
        $policy = file_get_contents($path);

        if (! is_string($policy) || trim($policy) === '') {
            throw new RuntimeException('Missing text moderation prompt at '.$path);
        }

        return $policy;
    }

    /**
     * Upload through the resumable Files API.
     *
     * @return array{name: string, uri: string}
     */
    private function uploadFile(UploadedFile $file, int $timeout): array
    {
        $size = (string) $file->getSize();
        $mimeType = (string) $file->getMimeType();

        $start = $this->request($timeout)
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'resumable',
                'X-Goog-Upload-Command' => 'start',
                'X-Goog-Upload-Header-Content-Length' => $size,
                'X-Goog-Upload-Header-Content-Type' => $mimeType,
            ])
            ->post($this->uploadUrl(), [
                'file' => ['display_name' => 'moderation-'.uniqid()],
            ]);

        if ($start->failed()) {
            throw new RuntimeException('Gemini file upload could not be started: '.$start->body());
        }

        $target = $start->header('X-Goog-Upload-URL');

        if ($target === '') {
            throw new RuntimeException('Gemini did not return an upload URL.');
        }

        $upload = $this->request($timeout)
            ->withHeaders([
                'Content-Length' => $size,
                'X-Goog-Upload-Offset' => '0',
                'X-Goog-Upload-Command' => 'upload, finalize',
            ])
            ->withBody($file->getContent(), $mimeType)
            ->post($target);

        if ($upload->failed()) {
            throw new RuntimeException('Gemini file upload failed: '.$upload->body());
        }

        $name = data_get($upload->json(), 'file.name');
        $uri = data_get($upload->json(), 'file.uri');

        if (! is_string($name) || ! is_string($uri)) {
            throw new RuntimeException('Gemini file upload returned no file reference.');
        }

        return ['name' => $name, 'uri' => $uri];
    }

    /**
     * Video is not immediately referenceable, so wait for it to leave PROCESSING.
     */
    private function awaitProcessed(string $name, int $timeout): void
    {
        $attempts = max(1, (int) ($this->config['file_poll_attempts'] ?? 20));
        $seconds = max(1, (int) ($this->config['file_poll_seconds'] ?? 2));

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            $state = data_get($this->request($timeout)->get($this->fileUrl($name))->json(), 'state');

            if ($state === 'ACTIVE') {
                return;
            }

            if ($state === 'FAILED') {
                throw new RuntimeException("Gemini failed to process the uploaded file [{$name}].");
            }

            sleep($seconds);
        }

        throw new RuntimeException("Gemini did not finish processing the uploaded file [{$name}] in time.");
    }

    private function deleteRemoteFile(string $name, int $timeout): void
    {
        // Best effort: files expire on Google's side anyway, and a cleanup
        // failure must not mask the verdict we are in the middle of returning.
        rescue(fn () => $this->request($timeout)->delete($this->fileUrl($name)), report: false);
    }

    /**
     * Every configurable category is turned off so we get a classification
     * rather than a refusal. The non-configurable ones still apply.
     *
     * @return list<array<string, string>>
     */
    private function safetySettings(): array
    {
        $categories = [
            'HARM_CATEGORY_HARASSMENT',
            'HARM_CATEGORY_HATE_SPEECH',
            'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'HARM_CATEGORY_DANGEROUS_CONTENT',
        ];

        return array_map(static fn (string $category): array => [
            'category' => $category,
            'threshold' => 'BLOCK_NONE',
        ], $categories);
    }

    private function request(int $timeout): PendingRequest
    {
        return Http::withHeaders(['x-goog-api-key' => (string) $this->config['api_key']])
            ->connectTimeout(10)
            ->timeout($timeout);
    }

    private function timeoutFor(bool $fullVideoFile = false): int
    {
        if ($fullVideoFile) {
            return max(60, (int) ($this->config['video_timeout'] ?? 120));
        }

        return max(10, (int) ($this->config['timeout'] ?? 30));
    }

    private function isVideo(UploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'video/');
    }

    private function modelUrl(): string
    {
        return "{$this->baseUrl()}/{$this->apiVersion()}/models/{$this->model()}:generateContent";
    }

    private function uploadUrl(): string
    {
        return "{$this->baseUrl()}/upload/{$this->apiVersion()}/files";
    }

    private function fileUrl(string $name): string
    {
        return "{$this->baseUrl()}/{$this->apiVersion()}/".ltrim($name, '/');
    }

    private function baseUrl(): string
    {
        return rtrim((string) ($this->config['base_url'] ?? 'https://generativelanguage.googleapis.com'), '/');
    }

    private function apiVersion(): string
    {
        return trim((string) ($this->config['api_version'] ?? 'v1beta'), '/');
    }

    private function model(): string
    {
        return (string) ($this->config['model'] ?? 'gemini-2.5-flash-lite');
    }

    private function inlineMaxBytes(): int
    {
        return max(1, (int) ($this->config['inline_max_kb'] ?? 15360)) * 1024;
    }
}
