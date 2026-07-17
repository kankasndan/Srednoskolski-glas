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

];
