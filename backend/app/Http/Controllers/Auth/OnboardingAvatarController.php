<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\ContentModerator;
use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Resources\MeResource;
use App\Models\MediaUpload;
use App\Services\Avatars\AvatarGenerationFailed;
use App\Services\Avatars\GeminiAvatarGenerator;
use App\Services\Media\ModeratedMediaStorage;
use App\Support\MediaLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Throwable;

class OnboardingAvatarController extends Controller
{
    private const REJECTED_MESSAGE = 'Оваа датотека не е дозволена.';

    private const UNAVAILABLE_MESSAGE = 'Проверката на датотеката не успеа. Обиди се повторно.';

    private const GENERATION_FAILED_MESSAGE = 'Не успеавме да го создадеме аватарот. Обиди се повторно.';

    public function store(
        Request $request,
        GeminiAvatarGenerator $generator,
        MediaStorage $storage,
        ContentModerator $moderator,
    ): JsonResponse {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.MediaLimits::maxKilobytesForMime('image/jpeg'),
                'mimetypes:image/jpeg,image/png,image/webp',
            ],
        ]);

        $file = $request->file('file');

        if (MediaLimits::exceedsSize($file)) {
            throw ValidationException::withMessages([
                'file' => [MediaLimits::sizeError((string) $file->getMimeType())],
            ]);
        }

        MediaLimits::assertDailyQuota((int) $request->user()->id);

        set_time_limit(120);

        $this->assertPhotoAllowed($file, $moderator);

        try {
            $generated = $generator->fromPhoto($file);
        } catch (AvatarGenerationFailed $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'file' => [$exception->userMessage],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'file' => [self::GENERATION_FAILED_MESSAGE],
            ]);
        }

        $portrait = $this->asUploadedFile($generated['bytes'], $generated['mime']);

        // The source photo was already screened. Running Gemini again on the
        // cartoon it just drew is a second 503/timeout with no extra safety.
        $uploader = $storage instanceof ModeratedMediaStorage ? $storage->inner() : $storage;

        try {
            $media = $uploader->upload($portrait, 'avatars');
        } finally {
            @unlink($portrait->getPathname());
        }

        MediaUpload::query()->create([
            'user_id' => $request->user()->id,
            'provider' => $media->provider,
            'file_id' => $media->id,
            'path' => $media->path,
            'url' => $media->url,
            'directory' => 'avatars',
        ]);

        $user = $request->user();
        $user->imageUrl = $media->url;
        $user->save();

        return response()->json([
            'url' => $media->url,
            'user' => (new MeResource($user->fresh([
                'studentData.school.city',
                'studentData.school.forum',
                'studentData.vocation',
            ])))->resolve(),
        ], 201);
    }

    private function assertPhotoAllowed(UploadedFile $file, ContentModerator $moderator): void
    {
        if (! (bool) config('moderation.enabled')) {
            return;
        }

        try {
            $verdict = $moderator->review($file);
        } catch (Throwable $exception) {
            report($exception);

            $busy = str_contains($exception->getMessage(), '503')
                || str_contains($exception->getMessage(), 'UNAVAILABLE')
                || str_contains($exception->getMessage(), 'high demand');

            throw ValidationException::withMessages([
                'file' => [$busy
                    ? 'Gemini е зафатен. Почекај малку и обиди се повторно.'
                    : self::UNAVAILABLE_MESSAGE],
            ]);
        }

        if ($verdict->isAllowed()) {
            return;
        }

        throw ValidationException::withMessages([
            'file' => [self::REJECTED_MESSAGE],
        ]);
    }

    private function asUploadedFile(string $bytes, string $mime): UploadedFile
    {
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };

        $base = tempnam(sys_get_temp_dir(), 'avatar');
        @unlink($base);
        $path = $base.'.'.$extension;
        file_put_contents($path, $bytes);

        return new UploadedFile($path, 'avatar.'.$extension, $mime, UPLOAD_ERR_OK, true);
    }
}
