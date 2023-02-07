<?php

namespace App\DTO;

class PlayerRankInfo
{
	public function __construct(
		public int $id,
		public string $username,
		public string $externalId,
		public string $rank
	) {
	}
}