<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;
use App\Helper\DiscordAvatarHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class ArcaneLeaderboardProvider implements LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     *
     * @throws GuzzleException
     */
    public static function fetchPlayers(): array
    {
        $client = new Client();
        $response = $client->get($_ENV['LEADERBOARD_URL'], [
            'query' => [
                'limit' => 1000,
            ],
            'headers' => [
                'Authorization' => $_ENV['LEADERBOARD_AUTH'],
            ]
        ]);
        $decoded = json_decode($response->getBody()->getContents(), true);
        $externalPlayers = [];
        foreach ($decoded['levels'] as $player) {
            $externalPlayers[] = new ExternalPlayer(
                $player['id'],
                $player['username'],
                DiscordAvatarHelper::resolveAvatarUrl($player['id'], $player['avatar']),
                $player['level'],
                self::calculateTotalXp($player['level'], $player['xp']),
                $player['messages'],
            );
        }

        return $externalPlayers;
    }

    protected static function calculateTotalXp(int $level, int $xp): int
    {
        for ($i = 0; $i < $level; $i++) {
            $xp += 75 + $i * 100;
        }

        return $xp;
    }

    public static function getProviderName(): string
    {
        return 'arcane';
    }
}
