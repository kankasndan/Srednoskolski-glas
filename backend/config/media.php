<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Media Driver
    |--------------------------------------------------------------------------
    |
    | The storage provider used for images, videos and files. Swapping the
    | backing service (e.g. to S3) is a matter of changing this value; the
    | rest of the application talks to the "MediaStorage" contract only.
    |
    | Supported: "imagekit", "s3"
    |
    */

    'default' => env('MEDIA_DRIVER', 'imagekit'),

    /*
    |--------------------------------------------------------------------------
    | Default Upload Directory
    |--------------------------------------------------------------------------
    |
    | Base folder/prefix used when no explicit directory is passed to an
    | upload. For ImageKit this maps to a "folder", for S3 to a key prefix.
    |
    */

    'directory' => env('MEDIA_DIRECTORY', 'uploads'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configuration
    |--------------------------------------------------------------------------
    */

    'drivers' => [

        'imagekit' => [
            'public_key' => env('IMAGEKIT_PUBLIC_KEY'),
            'private_key' => env('IMAGEKIT_PRIVATE_KEY'),
            'url_endpoint' => env('IMAGEKIT_URL_ENDPOINT'),
            'upload_endpoint' => env('IMAGEKIT_UPLOAD_ENDPOINT', 'https://upload.imagekit.io/api/v1/files/upload'),
            'api_endpoint' => env('IMAGEKIT_API_ENDPOINT', 'https://api.imagekit.io/v1'),
            'use_unique_file_name' => env('IMAGEKIT_UNIQUE_FILE_NAME', true),
        ],

        's3' => [
            'disk' => env('MEDIA_S3_DISK', 's3'),
            'visibility' => env('MEDIA_S3_VISIBILITY', 'public'),
        ],

    ],

];
