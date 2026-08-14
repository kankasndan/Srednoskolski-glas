<?php

namespace App\Support;

use App\Models\MediaUpload;
use App\Models\ThreadAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

final class MediaLimits
{
    public static function maxKilobytesForMime(string $mime): int
    {
        if (str_starts_with($mime, 'image/')) {
            return (int) config('media.limits.image_kb', 5120);
        }

        if (str_starts_with($mime, 'video/')) {
            return (int) config('media.limits.video_kb', 51200);
        }

        return (int) config('media.limits.document_kb', 10240);
    }

    public static function maxKilobytes(): int
    {
        return max(
            (int) config('media.limits.image_kb', 5120),
            (int) config('media.limits.video_kb', 51200),
            (int) config('media.limits.document_kb', 10240),
        );
    }

    public static function sizeError(string $mime): string
    {
        $mb = (int) ceil(self::maxKilobytesForMime($mime) / 1024);

        if (str_starts_with($mime, 'image/')) {
            return "Сликата е преголема. Максимум е {$mb} MB.";
        }

        if (str_starts_with($mime, 'video/')) {
            return "Видеото е преголемо. Максимум е {$mb} MB.";
        }

        return "Датотеката е преголема. Максимум е {$mb} MB.";
    }

    public static function exceedsSize(UploadedFile $file): bool
    {
        $mime = (string) $file->getMimeType();
        $maxBytes = self::maxKilobytesForMime($mime) * 1024;

        return $file->getSize() > $maxBytes;
    }

    public static function assertDailyQuota(int $userId, int $incoming = 1): void
    {
        $limit = (int) config('media.limits.daily_uploads', 30);
        $used = self::uploadsToday($userId);

        if (($used + $incoming) > $limit) {
            throw ValidationException::withMessages([
                'file' => ["Достигнат е дневниот лимит од {$limit} прикачувања."],
            ]);
        }
    }

    public static function uploadsToday(int $userId): int
    {
        $since = now()->startOfDay();

        $standalone = MediaUpload::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->count();

        $attached = ThreadAttachment::query()
            ->whereNotNull('file_id')
            ->where('created_at', '>=', $since)
            ->whereHas('thread', fn ($query) => $query->where('user_id', $userId))
            ->count();

        return $standalone + $attached;
    }
}
