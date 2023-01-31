<?php

namespace App\Service;

use App\Entity\Player;
use Carbon\Carbon;

final class PlayerRanksResolver
{
    private static array $ranks = [
        0 => 'Master',
        1 => 'Diamond',
        2 => 'Platinum',
        3 => 'Gold',
        4 => 'Silver',
        5 => 'Bronze',
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
     * @param Player[] $players
     *
     * @return array<string, Player[]>
     */
    public static function resolvePlayerRanks(array $players): array
    {
        $players = self::sortEligiblePlayersByDailyXp($players);
        $playerCount = count($players);
        $ranks = [];
        foreach (self::$ranks as $rank) {
            $ranks[$rank] = [];
        }

        $offset = 0;
        foreach (self::$standardRankDistribution as $rank => $distribution) {
            $currentRankPlayerCount = (int) round($distribution * $playerCount);
            $currentRankPlayers = array_slice($players, $offset, $currentRankPlayerCount);
            $ranks[self::$ranks[$rank]] = $currentRankPlayers;
            $offset += $currentRankPlayerCount;
        }

        return $ranks;
    }

    /**
     * @param Player[] $players
     *
     * @return Player[]
     */
    private static function sortEligiblePlayersByDailyXp(array $players): array
    {
        // Filter out players with less than 7 days of snapshots and latest snapshot older than 3 days
        $players = array_filter($players, function (Player $player) {
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

        usort($players, function (Player $a, Player $b) {
            $aDailyXp = self::calculateDailyXp($a);
            $bDailyXp = self::calculateDailyXp($b);

            return $bDailyXp <=> $aDailyXp;
        });

        return $players;
    }

    private static function calculateDailyXp(Player $player): float
    {
        $snapshots = $player->getSnapshots();
        $oldestSnapshot = $snapshots->last();
        $newestSnapshot = $snapshots->first();
        $daysBetweenSnapshots = $newestSnapshot->getCreatedAt()->diffInDays($oldestSnapshot->getCreatedAt());
        $xpDifference = $newestSnapshot->getXp() - $oldestSnapshot->getXp();

        return $xpDifference / $daysBetweenSnapshots;
    }
}
