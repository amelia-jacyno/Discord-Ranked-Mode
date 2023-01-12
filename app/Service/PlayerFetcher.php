<?php

namespace App\Service;

use GuzzleHttp\Client;

class PlayerFetcher
{
	protected const LEADERBOARD_URL = 'https://mee6.xyz/api/plugins/levels/leaderboard/364447643394506772';

	public static function fetchPlayers(): array
	{
		$client = new Client();
		$response = $client->get(self::LEADERBOARD_URL, [
			'query' => [
				'limit' => 1000,
			],
		]);
		$decoded = json_decode($response->getBody()->getContents(), true);

		return $decoded['players'];
	}
}