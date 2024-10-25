<?php

namespace App\Controller;

use App\DTO;
use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;
use App\Service\PlayerRanksResolver;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeaderboardController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/', name: 'home')]
    #[Route('/leaderboard', name: 'leaderboard')]
    public function leaderboard(): Response
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

    #[Route('/ranks', name: 'ranks')]
    public function ranks(): Response
    {
        $players = $this->playerRepository->getPlayersWithAMonthOfSnapshots();

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
    #[Route('/player/{playerId}', name: 'player')]
    public function player(Request $request): Response
    {
        $playerId = $request->get('playerId');

        if (null === $playerId) {
            header('Location: /');
            exit;
        }

        /** @var Entity\Player $player */
        $player = $this->playerRepository->find($playerId);

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
