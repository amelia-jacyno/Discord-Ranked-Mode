<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;

interface LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     */
    public static function fetchPlayers(string $url, ?string $authToken = null): array;

    public static function getProviderName(): string;
}
