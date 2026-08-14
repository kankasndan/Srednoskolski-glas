<?php

namespace App\Http\Controllers;

use App\Contracts\MediaStorage;
use App\Models\MediaUpload;
use App\Models\ThreadAttachment;
use App\Support\MediaLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    /** Allowlisted folder prefixes for standalone uploads. */
    private const ALLOWED_DIRECTORIES = [
        'uploads',
        'avatars',
        'threads',
    ];

    public function __construct(private readonly MediaStorage $storage) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.MediaLimits::maxKilobytes(),
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'directory' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(self::ALLOWED_DIRECTORIES),
            ],
        ]);

        $file = $request->file('file');
        if (MediaLimits::exceedsSize($file)) {
            throw ValidationException::withMessages([
                'file' => [MediaLimits::sizeError((string) $file->getMimeType())],
            ]);
        }

        MediaLimits::assertDailyQuota((int) $request->user()->id);

        $directory = $validated['directory'] ?? config('media.directory');

        $media = $this->storage->upload(
            $request->file('file'),
            $directory,
        );

        MediaUpload::query()->create([
            'user_id' => $request->user()->id,
            'provider' => $media->provider,
            'file_id' => $media->id,
            'path' => $media->path,
            'url' => $media->url,
            'directory' => $directory,
        ]);

        return response()->json($media, 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
        ]);

        $fileId = $validated['id'];
        $userId = (int) $request->user()->id;

        $ownedUpload = MediaUpload::query()
            ->where('user_id', $userId)
            ->where('file_id', $fileId)
            ->first();

        $ownsAttachment = ThreadAttachment::query()
            ->where('file_id', $fileId)
            ->whereHas('thread', fn ($query) => $query->where('user_id', $userId))
            ->exists();

        abort_unless($ownedUpload !== null || $ownsAttachment, 403);

        $deleted = $this->storage->delete($fileId);

        if ($deleted && $ownedUpload !== null) {
            $ownedUpload->delete();
        }

        if ($deleted && $ownsAttachment) {
            ThreadAttachment::query()
                ->where('file_id', $fileId)
                ->whereHas('thread', fn ($query) => $query->where('user_id', $userId))
                ->delete();
        }

        return response()->json([
            'deleted' => $deleted,
        ]);
    }
}
