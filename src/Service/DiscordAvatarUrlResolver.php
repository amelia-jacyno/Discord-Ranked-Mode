<?php

namespace App\Service;

final class DiscordAvatarUrlResolver
{
    public static function resolveAvatarUrl(string $externalId, ?string $avatar): string
    {
        if ($avatar === null) {
            return 'https://cdn.discordapp.com/embed/avatars/0.png';
        }

        return 'https://cdn.discordapp.com/avatars/' . $externalId . '/' . $avatar;
    }
}