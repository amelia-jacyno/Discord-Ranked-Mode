<?php

namespace App\Helper;

final class DiscordAvatarHelper
{
    public static function resolveAvatarUrl(string $externalId, ?string $avatar): string
    {
        if ($avatar === null) {
            return 'https://cdn.discordapp.com/embed/avatars/0.png';
        }

        return 'https://cdn.discordapp.com/avatars/' . $externalId . '/' . $avatar;
    }
}