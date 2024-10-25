<?php

namespace App\Controller;

use App\Entity;
use App\DTO;
use App\Repository\PlayerRepository;
use App\Service\Doctrine\EntityManagerProvider;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;
use App\Service\PlayerRanksResolver;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LeaderboardController extends AbstractController
{
    /**
     * @throws \Exception
     */
    public static function leaderboard(): Response
    {
        $externalPlayers = LeaderboardProviderResolver::resolveProvider($_ENV['LEADERBOARD_PROVIDER'] ?? 'mee6')::fetchPlayers();
        usort($externalPlayers, fn (DTO\ExternalPlayer $p1, DTO\ExternalPlayer $p2) => $p2->xp <=> $p1->xp);
        $externalPlayers = array_slice($externalPlayers, 0, 100);

        $hasMessageCounts = false;
        foreach ($externalPlayers as $externalPlayer) {
            if (null !== $externalPlayer->messageCount) {
                $hasMessageCounts = true;
                break;
            }
        }

        return self::render('leaderboard.html.twig', [
            'externalPlayers' => $externalPlayers,
            'hasMessageCounts' => $hasMessageCounts,
        ]);
    }

    public static function ranks(): Response
    {
        $entityManager = EntityManagerProvider::getEntityManager();
        $playerRepository = new PlayerRepository($entityManager);
        $players = $playerRepository->getPlayersWithAMonthOfSnapshots();

        $playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($players);
        /** @var array<string, array<DTO\PlayerRankInfo>> $ranks */
        $ranks = [];
        foreach ($playerRankInfos as $playerRankInfo) {
            $ranks[$playerRankInfo->rank->getName()][] = $playerRankInfo;
        }

        return self::render('ranks.html.twig', [
            'leaderboard' => $ranks,
        ]);
    }

    /**
     * @throws MissingMappingDriverImplementation
     * @throws Exception
     */
    public static function player(Request $request): Response
    {

        $entityManager = EntityManagerProvider::getEntityManager();
        $playerRepository = new PlayerRepository($entityManager);

        $playerId = $request->get('playerId');

        if (null === $playerId) {
            header('Location: /');
            exit;
        }

        /** @var Entity\Player $player */
        $player = $playerRepository->find($playerId);

        if (null === $player) {
            header('Location: /');
            exit;
        }

        $playerSnapshots = $player->getSnapshots();
        $playerSnapshots->filter(function (Entity\PlayerSnapshot $snapshot) {
            return $snapshot->getCreatedAt()->isAfter(Carbon::now()->subDays(7));
        });

        $snapshotDays = [];
        foreach ($playerSnapshots as $snapshot) {
            $snapshotDays[$snapshot->getCreatedAt()->format('Y-m-d')] = $snapshot;
        }

        $xpData = [];
        $chartDay = Carbon::now()->subDays(7);
        $previousXp = null;
        while ($chartDay->isBefore(Carbon::now())) {
            $snapshot = $snapshotDays[$chartDay->format('Y-m-d')] ?? null;
            if (null === $snapshot) {
                $xpData[] = [
                    'date' => $chartDay->format('Y-m-d'),
                    'xp' => null,
                ];
                $chartDay->addDay();

                continue;
            }

            if (null === $previousXp) {
                $previousXp = $snapshot->getXp();

                continue;
            }

            $xpData[] = [
                'date' => $snapshot->getCreatedAt()->format('Y-m-d'),
                'xp' => $snapshot->getXp() - $previousXp,
            ];
            $previousXp = $snapshot->getXp();

            $chartDay->addDay();
        }

        return self::render('player.html.twig', [
            'player' => $player,
            'xpData' => $xpData,
        ]);
    }
}