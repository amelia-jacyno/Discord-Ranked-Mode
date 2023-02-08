<?php

namespace App\Service;

use App\DTO;
use App\Entity;
use App\Enum;
use Carbon\Carbon;

final class PlayerRanksResolver
{
    private static array $ranks = [
        0 => Enum\Rank::Master,
		1 => Enum\Rank::Diamond,
		2 => Enum\Rank::Platinum,
		3 => Enum\Rank::Gold,
		4 => Enum\Rank::Silver,
		5 => Enum\Rank::Bronze,
    ];

    private static array $standardRankDistribution = [
        0 => 0.05,
        1 => 0.1,
        2 => 0.2,
        3 => 0.35,
        4 => 0.2,
        5 => 0.1,
    ];

    /**
     * @param Entity\Player[] $players
     *
     * @return DTO\PlayerRankInfo[]
     */
    public static function resolvePlayerRanks(array $players): array
    {
        $players = self::sortEligiblePlayersByDailyXp($players);
        $playerCount = count($players);

		$playerRankInfos = [];
        $offset = 0;
        foreach (self::$standardRankDistribution as $rankId => $distribution) {
            $currentRankPlayerCount = (int) round($distribution * $playerCount);
            $currentRankPlayers = array_slice($players, $offset, $currentRankPlayerCount);
			foreach ($currentRankPlayers as $player) {
				$playerRankInfos[] = new DTO\PlayerRankInfo(
					$player->getId(),
					$player->getUsername(),
					$player->getExternalId(),
					self::$ranks[$rankId],
					self::calculateDailyXp($player)
				);
			}

            $offset += $currentRankPlayerCount;
        }

        return $playerRankInfos;
    }

    /**
     * @param Entity\Player[] $players
     *
     * @return Entity\Player[]
     */
    private static function sortEligiblePlayersByDailyXp(array $players): array
    {
        // Filter out players with less than 7 days of snapshots and latest snapshot older than 3 days
        $players = array_filter($players, function (Entity\Player $player) {
            if ($player->getSnapshots()->count() < 2) {
                return false;
            }

            $newestSnapshot = $player->getSnapshots()->first();
            $oldestSnapshot = $player->getSnapshots()->last();
            if ($newestSnapshot->getCreatedAt()->diffInDays($oldestSnapshot->getCreatedAt()) < 6) {
                return false;
            }

            if ($newestSnapshot->getCreatedAt()->diffInDays(Carbon::now()) > 3) {
                return false;
            }

            if ($newestSnapshot->getXp() === $oldestSnapshot->getXp()) {
                return false;
            }

            return true;
        });

        usort($players, function (Entity\Player $a, Entity\Player $b) {
            $aDailyXp = self::calculateDailyXp($a);
            $bDailyXp = self::calculateDailyXp($b);

            return $bDailyXp <=> $aDailyXp;
        });

        return $players;
    }

    private static function calculateDailyXp(Entity\Player $player): float
    {
        $snapshots = $player->getSnapshots();
        $oldestSnapshot = $snapshots->last();
        $newestSnapshot = $snapshots->first();
        $daysBetweenSnapshots = $newestSnapshot->getCreatedAt()->diffInDays($oldestSnapshot->getCreatedAt());
        $xpDifference = $newestSnapshot->getXp() - $oldestSnapshot->getXp();

        return $xpDifference / $daysBetweenSnapshots;
    }
}
