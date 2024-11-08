<?php

namespace App\Service;

use App\DTO;
use App\Entity;
use App\Enum;
use App\Helper\DiscordAvatarHelper;
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
     * @param DTO\PlayerWithSnapshotData[] $playersData
     *
     * @return DTO\PlayerRankInfo[]
     */
    public static function resolvePlayerRanks(array $playersData): array
    {
        $playersData = self::filterEligiblePlayers($playersData);
        $playersData = self::sortPlayersByDailyXp($playersData);
        $playerCount = count($playersData);

        $playerRankInfos = [];
        $offset = 0;
        foreach (self::$standardRankDistribution as $rankId => $distribution) {
            $currentRankPlayerCount = (int) round($distribution * $playerCount);
            $currentRankPlayersData = array_slice($playersData, $offset, $currentRankPlayerCount);
            foreach ($currentRankPlayersData as $playerData) {
                $avatarUrl = filter_var($playerData->avatar, FILTER_VALIDATE_URL)
                    ? $playerData->avatar
                    : DiscordAvatarHelper::resolveAvatarUrl($playerData->externalId, $playerData->avatar);
                $playerRankInfos[] = new DTO\PlayerRankInfo(
                    $playerData->id,
                    $avatarUrl,
                    $playerData->username,
                    $playerData->externalId,
                    self::$ranks[$rankId],
                    self::calculateDailyXp($playerData)
                );
            }

            $offset += $currentRankPlayerCount;
        }

        return $playerRankInfos;
    }

    private static function filterEligiblePlayers(array $playersData): array
    {
        return array_filter($playersData, function (DTO\PlayerWithSnapshotData $playerData) {
            if ($playerData->newestSnapshotDate->diffInDays($playerData->oldestSnapshotDate) < 6) {
                return false;
            }

            if ($playerData->newestSnapshotDate->diffInDays(Carbon::now()) > 3) {
                return false;
            }

            if ($playerData->newestSnapshotXp === $playerData->oldestSnapshotXp) {
                return false;
            }

            return true;
        });
    }

    /**
     * @param DTO\PlayerWithSnapshotData[] $playersData
     *
     * @return Entity\Player[]
     */
    private static function sortPlayersByDailyXp(array $playersData): array
    {
        usort($playersData, function (DTO\PlayerWithSnapshotData $a, DTO\PlayerWithSnapshotData $b) {
            $aDailyXp = self::calculateDailyXp($a);
            $bDailyXp = self::calculateDailyXp($b);

            return $bDailyXp <=> $aDailyXp;
        });

        return $playersData;
    }

    private static function calculateDailyXp(DTO\PlayerWithSnapshotData $playerData): float
    {
        $daysBetweenSnapshots = $playerData->newestSnapshotDate->diffInDays($playerData->oldestSnapshotDate);
        $xpDifference = $playerData->newestSnapshotXp - $playerData->oldestSnapshotXp;

        return $xpDifference / $daysBetweenSnapshots;
    }
}
