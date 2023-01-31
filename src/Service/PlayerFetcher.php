<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class PlayerFetcher
{
    /**
     * @throws GuzzleException
     */
    public static function fetchPlayers(): array
    {
        $client = new Client();
        $response = $client->get($_ENV['LEADERBOARD_URL'], [
            'query' => [
                'limit' => 1000,
            ],
        ]);
        $decoded = json_decode($response->getBody()->getContents(), true);

        return $decoded['players'];
    }
}
