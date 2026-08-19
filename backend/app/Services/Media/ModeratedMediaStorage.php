<?php

namespace App\Services\Media;

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use App\Support\Media\StoredMedia;
use App\Support\Moderation\ModerationDecision;
use App\Support\Moderation\ModerationVerdict;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Screens files before they are handed to the backing media driver.
 *
 * The inner driver is ImageKit or S3; this wrapper is what every upload path
 * actually talks to, so a refused file never receives a public URL.
 */
final class ModeratedMediaStorage implements MediaStorage
{
    private const REJECTED_MESSAGE = 'Оваа датотека не е дозволена.';

    private const UNAVAILABLE_MESSAGE = 'Проверката на датотеката не успеа. Обиди се повторно.';

    /**
     * @param  Closure(): ContentModerator  $moderator
     */
    public function __construct(
        private readonly MediaStorage $inner,
        private readonly Closure $moderator,
    ) {}

    public function inner(): MediaStorage
    {
        return $this->inner;
    }

    public function upload(UploadedFile $file, ?string $directory = null, array $options = []): StoredMedia
    {
        if (str_starts_with((string) $file->getMimeType(), 'video/')) {
            set_time_limit(180);
        }

        if ($this->shouldReview($file)) {
            $this->enforce($file);
        }

        return $this->inner->upload($file, $directory, $options);
    }

    public function delete(StoredMedia|string $media): bool
    {
        return $this->inner->delete($media);
    }

    public function url(string $path, array $options = []): string
    {
        return $this->inner->url($path, $options);
    }

    private function shouldReview(UploadedFile $file): bool
    {
        if (! (bool) config('moderation.enabled')) {
            return false;
        }

        $mime = (string) $file->getMimeType();

        return str_starts_with($mime, 'image/')
            || str_starts_with($mime, 'video/')
            || $mime === 'application/pdf';
    }

    private function enforce(UploadedFile $file): void
    {
        try {
            $verdict = ($this->moderator)()->review($file);
        } catch (Throwable $exception) {
            $this->handleFailure($file, $exception);

            return;
        }

        $this->log($file, $verdict);

        if ($verdict->isAllowed()) {
            return;
        }

        throw ValidationException::withMessages($this->errorBag(self::REJECTED_MESSAGE));
    }

    private function handleFailure(UploadedFile $file, Throwable $exception): void
    {
        report($exception);

        $this->channel()->warning('content_moderation.unavailable', $this->fileContext($file) + [
            'exception' => $exception->getMessage(),
        ]);

        if ((string) config('moderation.on_failure', 'reject') === 'allow') {
            return;
        }

        $message = self::UNAVAILABLE_MESSAGE;

        if (app()->hasDebugModeEnabled() && ! app()->runningUnitTests()) {
            $message .= ' ('.$exception->getMessage().')';
        }

        throw ValidationException::withMessages($this->errorBag($message));
    }

    private function log(UploadedFile $file, ModerationVerdict $verdict): void
    {
        $context = $this->fileContext($file) + [
            'decision' => $verdict->decision->value,
            'reason' => $verdict->reason,
            'categories' => $verdict->categories,
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
    private function fileContext(UploadedFile $file): array
    {
        return [
            'user_id' => auth()->id(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Attach the message to whichever file field is on the current request, so
     * thread compose (`files`), standalone media (`file`) and admin forms
     * (`image` / `icon` / `banner`) all surface it.
     *
     * @return array<string, list<string>>
     */
    private function errorBag(string $message): array
    {
        $fields = [];

        foreach (['file', 'files', 'image', 'icon', 'banner'] as $field) {
            if (request()?->hasFile($field)) {
                $fields[$field] = [$message];
            }
        }

        return $fields === [] ? ['file' => [$message]] : $fields;
    }

    private function channel(): LoggerInterface
    {
        return Log::channel((string) config('moderation.log_channel', 'moderation'));
    }
}
