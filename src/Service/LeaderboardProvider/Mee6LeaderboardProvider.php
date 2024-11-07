<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;
use App\Helper\DiscordAvatarHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class Mee6LeaderboardProvider implements LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     *
     * @throws GuzzleException
     */
    public static function fetchPlayers(string $url, ?string $authToken = null): array
    {
        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'limit' => 1000,
            ],
        ]);
        $decoded = json_decode($response->getBody()->getContents(), true);
        $externalPlayers = [];
        foreach ($decoded['players'] as $player) {
            $externalPlayers[] = new ExternalPlayer(
                $player['id'],
                $player['username'],
                DiscordAvatarHelper::resolveAvatarUrl($player['id'], $player['avatar']),
                $player['level'],
                $player['xp'],
                $player['message_count'],
            );
        }

        return $externalPlayers;
    }

    public static function getProviderName(): string
    {
        return 'mee6';
    }
}
