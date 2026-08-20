<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Avatars
    |--------------------------------------------------------------------------
    |
    | Users who don't upload their own picture get one of these assigned at
    | random on creation. These are static assets served by the Next.js
    | frontend from its `public/avatars` folder, so the values are the public
    | paths the browser requests (resolved against the frontend origin).
    |
    | Drop the matching files into `frontend/public/avatars/` using these exact
    | names (adjust the extension here if your files aren't .svg).
    |
    */

    'defaults' => [
        '/avatars/default-1.svg',
        '/avatars/default-2.svg',
        '/avatars/default-3.svg',
        '/avatars/default-4.svg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding avatar generation
    |--------------------------------------------------------------------------
    |
    | Edit `generation.prompt` below — that is the instruction Gemini receives
    | together with the photo the student uploads on /register/onboarding_2.
    | The original photo is never stored. Only the generated image is saved
    | to media storage and written to users.imageUrl.
    |
    */

    'generation' => [

        'model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),

        'timeout' => (int) env('GEMINI_IMAGE_TIMEOUT', 60),

        'prompt' => <<<'PROMPT'
Use the attached photo as the only reference. Fill [SUBJECT_DESCRIPTION], [EXPRESSION], [CLOTHING], and [OPTIONAL_ACCESSORIES] from what is actually visible in that photo. Do not invent a different person.

A 1:1 aspect ratio vector cartoon avatar illustration of [SUBJECT_DESCRIPTION], [EXPRESSION]. Modern streetwear sticker-art aesthetic with a crisp die-cut white outline around the subject. Framing: centered front-facing pose, direct gaze into camera, close-up head-and-shoulders portrait cropped strictly below the shoulders. Art style: thick black outlines with clean uniform line weight, flat vector cel shading, smooth flat color fills, soft blocked shadows on hair and clothing, minimal character gradients. Wearing [CLOTHING] and [OPTIONAL_ACCESSORIES]. Background: vivid vertical gradient background seamlessly blending hex colors #7B2FF7, #5B3FE0, and #2E6BE6, with no texture, no scenery, and no background objects. High contrast, crisp edges. Strict constraints: No hands visible, no blush on cheeks, no skin gradients, no background patterns.
PROMPT,

    ],

];
