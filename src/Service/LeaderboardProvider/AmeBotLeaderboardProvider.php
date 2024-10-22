<?php

namespace App\Service\LeaderboardProvider;

use App\DTO\ExternalPlayer;
use App\Helper\DiscordAvatarHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class AmeBotLeaderboardProvider implements LeaderboardProvider
{
    /**
     * @return ExternalPlayer[]
     *
     * @throws GuzzleException
     */
    public static function fetchPlayers(): array
    {
        $client = new Client();
        $response = $client->get($_ENV['LEADERBOARD_URL']);
        $decoded = json_decode($response->getBody()->getContents(), true);
        $externalPlayers = [];
        foreach ($decoded as $player) {
            $externalPlayers[] = new ExternalPlayer(
                $player['id'],
                $player['User']['username'],
                $player['User']['avatar'],
                $player['level'],
                $player['xp'],
                null,
            );
        }

        return $externalPlayers;
    }

    public static function getProviderName(): string
    {
        return 'amebot';
    }
}
