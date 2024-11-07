<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class AmeBotLeaderboardProvider implements LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     *
     * @throws GuzzleException
     */
    public static function fetchPlayers(string $url, ?string $authToken = null): array
    {
        $client = new Client();
        $response = $client->get($url);
        $decoded = json_decode($response->getBody()->getContents(), true);
        $externalPlayers = [];
        foreach ($decoded as $player) {
            $externalPlayers[] = new ExternalPlayer(
                $player['id'],
                $player['username'],
                $player['avatarUrl'],
                $player['level'],
                $player['xp'],
                $player['messageCount'],
            );
        }

        return $externalPlayers;
    }

    public static function getProviderName(): string
    {
        return 'amebot';
    }
}
