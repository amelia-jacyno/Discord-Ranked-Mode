<?php

namespace App\Enum;

enum Rank
{
	case Master;
	case Diamond;
	case Platinum;
	case Gold;
	case Silver;
	case Bronze;

	public function getName(): string
	{
		return match ($this) {
			self::Master => 'Master',
			self::Diamond => 'Diamond',
			self::Platinum => 'Platinum',
			self::Gold => 'Gold',
			self::Silver => 'Silver',
			self::Bronze => 'Bronze',
		};
	}

	public function getValue(): string
	{
		return match($this) {
			self::Master => 'master',
			self::Diamond => 'diamond',
			self::Platinum => 'platinum',
			self::Gold => 'gold',
			self::Silver => 'silver',
			self::Bronze => 'bronze',
		};
	}
}
