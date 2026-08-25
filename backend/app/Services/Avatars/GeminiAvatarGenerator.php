<?php

namespace App\Services\Avatars;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Turns an onboarding photo into a stylized avatar via Gemini image generation.
 *
 * The prompt lives in config/avatars.php → generation.prompt. Change that
 * string when you want a different look; this class only transports it.
 */
class GeminiAvatarGenerator
{
    /**
     * @return array{bytes: string, mime: string}
     */
    public function fromPhoto(UploadedFile $photo): array
    {
        if (! $photo->isValid()) {
            throw new RuntimeException($photo->getErrorMessage());
        }

        $apiKey = (string) config('moderation.drivers.gemini.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Missing Gemini configuration value [api_key]. Set GEMINI_API_KEY in your .env file.');
        }

        $prompt = trim((string) config('avatars.generation.prompt'));

        if ($prompt === '') {
            throw new RuntimeException('Missing avatar generation prompt. Set it in config/avatars.php under generation.prompt.');
        }

        $timeout = max(20, (int) config('avatars.generation.timeout', 60));
        $mimeType = (string) $photo->getMimeType();

        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->connectTimeout(10)
            ->timeout($timeout)
            ->retry(2, 250, function (Throwable $exception): bool {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                return $exception instanceof RequestException
                    && $exception->response->serverError();
            }, throw: false)
            ->post($this->modelUrl(), [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => base64_encode($photo->getContent()),
                            ],
                        ],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => [
                    // gemini-2.5-flash-image rejects imageConfig.aspectRatio
                    // ("Aspect ratio is not enabled for this model"). Square
                    // framing is asked for in the prompt instead.
                    'responseModalities' => ['TEXT', 'IMAGE'],
                ],
            ]);

        if ($response->failed()) {
            throw $this->failedRequest($response);
        }

        try {
            return $this->extractImage((array) $response->json());
        } catch (RuntimeException $exception) {
            throw new AvatarGenerationFailed(
                'Не успеавме да го создадеме аватарот. Обиди се повторно.',
                $exception->getMessage(),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{bytes: string, mime: string}
     */
    private function extractImage(array $payload): array
    {
        $blockReason = data_get($payload, 'promptFeedback.blockReason');

        if (is_string($blockReason) && $blockReason !== '') {
            throw new RuntimeException("Gemini refused to generate an avatar ({$blockReason}).");
        }

        foreach ((array) data_get($payload, 'candidates', []) as $candidate) {
            foreach ((array) data_get($candidate, 'content.parts', []) as $part) {
                if (! is_array($part)) {
                    continue;
                }

                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;

                if (! is_array($inline)) {
                    continue;
                }

                $data = $inline['data'] ?? null;
                $mime = $inline['mimeType'] ?? $inline['mime_type'] ?? 'image/png';

                if (! is_string($data) || $data === '') {
                    continue;
                }

                $bytes = base64_decode($data, true);

                if (! is_string($bytes) || $bytes === '') {
                    continue;
                }

                return [
                    'bytes' => $bytes,
                    'mime' => is_string($mime) && $mime !== '' ? $mime : 'image/png',
                ];
            }
        }

        throw new RuntimeException('Gemini returned no generated avatar image.');
    }

    private function failedRequest(Response $response): AvatarGenerationFailed
    {
        $body = $response->body();
        $status = $response->status();

        if ($status === 429) {
            $noImageQuota = str_contains($body, 'free_tier') && str_contains($body, 'limit: 0');

            return new AvatarGenerationFailed(
                $noImageQuota
                    ? 'Генерирањето аватари бара платена Gemini квота за слики. Провери billing за проектот на API клучот.'
                    : 'Gemini е зафатен. Почекај околу една минута и обиди се повторно.',
                'Gemini avatar generation failed ('.$status.'): '.$body,
            );
        }

        if ($status === 400 && str_contains($body, 'only supports text output')) {
            return new AvatarGenerationFailed(
                'GEMINI_IMAGE_MODEL мора да биде модел што црта слики (на пр. gemini-2.5-flash-image), не flash-lite.',
                'Gemini avatar generation failed ('.$status.'): '.$body,
            );
        }

        return new AvatarGenerationFailed(
            'Не успеавме да го создадеме аватарот. Обиди се повторно.',
            'Gemini avatar generation failed ('.$status.'): '.$body,
        );
    }

    private function modelUrl(): string
    {
        $base = rtrim((string) config('moderation.drivers.gemini.base_url', 'https://generativelanguage.googleapis.com'), '/');
        $version = trim((string) config('moderation.drivers.gemini.api_version', 'v1beta'), '/');
        $model = (string) config('avatars.generation.model', 'gemini-2.5-flash-image');

        return "{$base}/{$version}/models/{$model}:generateContent";
    }
}
