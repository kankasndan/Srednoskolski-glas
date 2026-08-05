<?php

use App\Providers\AppServiceProvider;
use App\Providers\MediaServiceProvider;

return [
    AppServiceProvider::class,
    MediaServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,

];
