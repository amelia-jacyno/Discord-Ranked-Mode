<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;

interface LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     */
    public static function fetchPlayers(): array;

    public static function getProviderName(): string;
}