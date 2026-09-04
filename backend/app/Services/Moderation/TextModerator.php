<?php

namespace App\Services\Moderation;

use App\Contracts\ContentModerator;
use App\Support\HtmlSanitizer;
use App\Support\Moderation\ModerationDecision;
use App\Support\Moderation\ModerationVerdict;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Screens student-authored text before it is saved.
 *
 * File uploads still go through ModeratedMediaStorage. This gate is the text
 * counterpart so a slur in a thread or comment cannot skip the same model.
 */
final class TextModerator
{
    private const REJECTED_MESSAGE = 'Овој текст не е дозволен. Отстрани навредливи зборови или говор на омраза.';

    private const UNAVAILABLE_MESSAGE = 'Проверката на текстот не успеа. Обиди се повторно.';

    /**
     * @param  array<string, mixed>  $validated
     */
    public function enforceThread(array $validated): void
    {
        $this->enforce(
            $this->assembleThread($validated),
            target: 'thread_text',
            fieldKeys: ['title', 'description', 'poll'],
            fallbackField: 'title',
        );
    }

    public function enforceComment(?string $content): void
    {
        $text = trim((string) $content);

        if ($text === '') {
            return;
        }

        $this->enforce(
            'Comment: '.$text,
            target: 'comment_text',
            fieldKeys: ['content'],
            fallbackField: 'content',
        );
    }

    /**
     * @param  list<string>  $fieldKeys
     */
    private function enforce(string $text, string $target, array $fieldKeys, string $fallbackField): void
    {
        if (! (bool) config('moderation.enabled')) {
            return;
        }

        if ($text === '') {
            return;
        }

        try {
            $verdict = app(ContentModerator::class)->reviewText($text);
        } catch (Throwable $exception) {
            $this->handleFailure($text, $target, $fallbackField, $exception);

            return;
        }

        $this->log($text, $target, $verdict);

        if ($verdict->isAllowed()) {
            return;
        }

        throw ValidationException::withMessages($this->errorBag(
            $verdict->fields,
            $fieldKeys,
            $fallbackField,
            $verdict->reason,
        ));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assembleThread(array $validated): string
    {
        $sections = [];
        $title = trim((string) ($validated['title'] ?? ''));

        if ($title !== '') {
            $sections[] = 'Title: '.$title;
        }

        $description = HtmlSanitizer::plainText($validated['description'] ?? null);

        if ($description !== '') {
            $sections[] = 'Description: '.$description;
        }

        if (empty($validated['remove_poll'])) {
            $poll = is_array($validated['poll'] ?? null) ? $validated['poll'] : [];
            $question = trim((string) ($poll['question'] ?? ''));

            if ($question !== '') {
                $sections[] = 'Poll question: '.$question;
            }

            foreach (array_values($poll['options'] ?? []) as $index => $label) {
                $option = trim((string) $label);

                if ($option !== '') {
                    $sections[] = 'Poll option '.($index + 1).': '.$option;
                }
            }
        }

        return implode("\n", $sections);
    }

    private function handleFailure(string $text, string $target, string $fallbackField, Throwable $exception): void
    {
        report($exception);

        $this->channel()->warning('content_moderation.unavailable', $this->textContext($text, $target) + [
            'exception' => $exception->getMessage(),
        ]);

        if ((string) config('moderation.on_failure', 'reject') === 'allow') {
            return;
        }

        $message = self::UNAVAILABLE_MESSAGE;

        if (app()->hasDebugModeEnabled() && ! app()->runningUnitTests()) {
            $message .= ' ('.$exception->getMessage().')';
        }

        throw ValidationException::withMessages([$fallbackField => [$message]]);
    }

    private function log(string $text, string $target, ModerationVerdict $verdict): void
    {
        $context = $this->textContext($text, $target) + [
            'decision' => $verdict->decision->value,
            'reason' => $verdict->reason,
            'categories' => $verdict->categories,
            'fields' => $verdict->fields,
        ];

        match ($verdict->decision) {
            ModerationDecision::Allow => $this->channel()->debug('content_moderation.allow', $context),
            ModerationDecision::Reject => $this->channel()->warning('content_moderation.reject', $context),
            ModerationDecision::Escalate => $this->channel()->alert('content_moderation.escalate', $context),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function textContext(string $text, string $target): array
    {
        return [
            'target' => $target,
            'user_id' => auth()->id(),
            'text' => $text,
        ];
    }

    /**
     * @param  list<string>  $fields
     * @param  list<string>  $fieldKeys
     * @return array<string, list<string>>
     */
    private function errorBag(array $fields, array $fieldKeys, string $fallbackField, ?string $reason): array
    {
        $keys = array_values(array_intersect($fields, $fieldKeys));

        if ($keys === []) {
            $keys = [$fallbackField];
        }

        $message = $this->userMessage($reason);
        $bag = [];

        foreach ($keys as $field) {
            $bag[$field] = [$message];
        }

        return $bag;
    }

    /**
     * Show the model's Macedonian explanation when it actually wrote one.
     * English log leftovers stay hidden behind the generic fallback.
     */
    private function userMessage(?string $reason): string
    {
        if (is_string($reason) && $reason !== '' && preg_match('/\p{Cyrillic}/u', $reason) === 1) {
            return $reason;
        }

        return self::REJECTED_MESSAGE;
    }

    private function channel(): LoggerInterface
    {
        return Log::channel((string) config('moderation.log_channel', 'moderation'));
    }
}
