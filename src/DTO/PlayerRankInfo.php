<?php

namespace App\DTO;

use App\Enum;

class PlayerRankInfo
{
	public function __construct(
		public int $id,
		public string $username,
		public string $externalId,
		public Enum\Rank $rank,
		public float $dailyXp,
	) {
	}
}