<?php

namespace App\Contracts;

use App\Support\Moderation\ModerationVerdict;
use Illuminate\Http\UploadedFile;

/**
 * Contract every content moderation provider must implement.
 *
 * Implementations decide only whether a file is acceptable. Turning a verdict
 * into a user-facing error, a log entry or an escalation is the caller's job.
 */
interface ContentModerator
{
    /**
     * Screen a file that has not been stored yet.
     *
     * Implementations must throw when they cannot reach a verdict, so the
     * caller's fail-open/fail-closed policy decides what happens next. They must
     * not throw merely because the file turned out to be disallowed.
     */
    public function review(UploadedFile $file): ModerationVerdict;
}
