<?php

namespace App\Services\Avatars;

use RuntimeException;

final class AvatarGenerationFailed extends RuntimeException
{
    public function __construct(
        public readonly string $userMessage,
        string $internalMessage = '',
    ) {
        parent::__construct($internalMessage !== '' ? $internalMessage : $userMessage);
    }
}
