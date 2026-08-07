<?php

use App\Providers\AppServiceProvider;
use App\Providers\MediaServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AppServiceProvider::class,
    MediaServiceProvider::class,
    PermissionServiceProvider::class,

];
