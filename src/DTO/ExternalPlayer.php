<?php

namespace App\DTO;

class ExternalPlayer
{
    public function __construct(
        public int $id,
        public string $username,
        public ?string $avatarUrl,
        public int $level,
        public int $xp,
        public ?int $messageCount,
    ) {
    }
}
