<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Moderation
    |--------------------------------------------------------------------------
    |
    | Screens every uploaded image, video and document before it is handed to
    | the media driver, so refused files never reach a public URL. The check is
    | wired in as a decorator around the "MediaStorage" contract, which means it
    | covers every upload path (thread attachments, admin forum art, avatars)
    | without callers knowing about it.
    |
    */

    'enabled' => env('CONTENT_MODERATION_ENABLED', true),

    /*
    | Supported: "gemini"
    */

    'driver' => env('CONTENT_MODERATION_DRIVER', 'gemini'),

    /*
    | What to do when the moderation service itself is unreachable, times out or
    | answers with something unusable — as opposed to answering "this file is
    | not allowed", which always blocks the upload.
    |
    | "reject" (fail closed) refuses the upload and asks the user to retry.
    | "allow"  (fail open)  lets the file through and logs a warning.
    |
    | Fail closed is the default: this platform is used by minors, so an
    | unscreened upload is a worse outcome than a failed one.
    */

    'on_failure' => env('CONTENT_MODERATION_ON_FAILURE', 'reject'),

    /*
    | Decisions are written here rather than to the application log, because
    | escalations need to be findable and retained independently of debug noise.
    */

    'log_channel' => env('CONTENT_MODERATION_LOG_CHANNEL', 'moderation'),

    'drivers' => [

        /*
        | IMPORTANT: use an API key from a project with billing enabled. Under
        | the Gemini API terms, content sent to the unpaid tier may be used to
        | improve Google's products and may be seen by human reviewers, which is
        | not an acceptable data path for photos uploaded by students. The
        | EEA/Switzerland/UK carve-out that applies paid terms to free usage does
        | not cover North Macedonia.
        |
        | @see https://ai.google.dev/gemini-api/terms
        */

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),

            // Google recommends Flash-Lite for moderation (cheapest and fastest
            // of the multimodal models). Newer Flash-Lite releases are drop-in.
            'model' => env('GEMINI_MODERATION_MODEL', 'gemini-2.5-flash-lite'),

            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'api_version' => env('GEMINI_API_VERSION', 'v1beta'),

            'timeout' => (int) env('GEMINI_MODERATION_TIMEOUT', 30),

            // How many stills to sample from a video. Three covers start / middle
            // / late without waiting on Gemini's video Files API.
            'video_frames' => (int) env('GEMINI_VIDEO_FRAMES', 3),

            // Optional absolute path to ffmpeg. Empty means: look on PATH, then
            // the ffmpeg-static npm package under the backend directory.
            'ffmpeg_path' => env('FFMPEG_PATH'),

            // Used only when stills cannot be sampled and the whole file has to
            // go through the Files API (PDFs, or videos with no ffmpeg).
            'video_timeout' => (int) env('GEMINI_VIDEO_TIMEOUT', 120),

            // Files at or below this size are sent inline as base64. Larger ones
            // go through the Files API, because a generateContent request has a
            // total payload ceiling of roughly 20 MB.
            'inline_max_kb' => (int) env('GEMINI_INLINE_MAX_KB', 15360),

            // The Files API needs a moment to process video before it can be
            // referenced, so uploads are polled until they report ACTIVE.
            'file_poll_attempts' => (int) env('GEMINI_FILE_POLL_ATTEMPTS', 20),
            'file_poll_seconds' => (int) env('GEMINI_FILE_POLL_SECONDS', 2),
        ],

    ],

];
