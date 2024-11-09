<?php

namespace App\Controller;

use App\DTO;
use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;
use App\Service\PlayerRanksResolver;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class LeaderboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/{guildId}', name: 'home', requirements: ['guildId' => '\d+'])]
    #[Route('{guildId}/leaderboard', name: 'leaderboard', requirements: ['guildId' => '\d+'])]
    public function leaderboard(string $guildId): Response
    {
        /** @var Entity\Guild $guild */
        $guild = $this->entityManager->getRepository(Entity\Guild::class)->findOneBy(['externalId' => $guildId]);

        if (!$guild || !$guild->getLeaderboardProvider()) {
            throw new NotFoundHttpException();
        }

        $externalPlayers = LeaderboardProviderResolver::resolveProvider($guild->getLeaderboardProvider())::fetchPlayers(
            $guild->getLeaderboardUrl(),
            $guild->getLeaderboardProviderAuthToken(),
        );
        usort($externalPlayers, fn (DTO\ExternalPlayer $p1, DTO\ExternalPlayer $p2) => $p2->xp <=> $p1->xp);
        $externalPlayers = array_slice($externalPlayers, 0, 100);

        $hasMessageCounts = false;
        foreach ($externalPlayers as $externalPlayer) {
            if (null !== $externalPlayer->messageCount) {
                $hasMessageCounts = true;
                break;
            }
        }

        return $this->render('leaderboard.html.twig', [
            'externalPlayers' => $externalPlayers,
            'hasMessageCounts' => $hasMessageCounts,
        ]);
    }

    #[Route('{guildId}/ranks', name: 'ranks', requirements: ['guildId' => '\d+'])]
    public function ranks(string $guildId): Response
    {
        $guild = $this->entityManager->getRepository(Entity\Guild::class)->findOneBy(['externalId' => $guildId]);

        if (!$guild) {
            throw new NotFoundHttpException();
        }

        $playersData = $this->playerRepository->getPlayersWithSnapshotData($guild);

        $playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($playersData);
        /** @var array<string, array<DTO\PlayerRankInfo>> $ranks */
        $ranks = [];
        foreach ($playerRankInfos as $playerRankInfo) {
            $ranks[$playerRankInfo->rank->getName()][] = $playerRankInfo;
        }

        return $this->render('ranks.html.twig', [
            'leaderboard' => $ranks,
        ]);
    }

    /**
     * @throws MissingMappingDriverImplementation
     * @throws Exception
     */
    #[Route('{guildId}/player/{playerId}', name: 'player', requirements: ['guildId' => '\d+', 'playerId' => '\d+'])]
    public function player(string $guildId, string $playerId): Response
    {
        $guild = $this->entityManager->getRepository(Entity\Guild::class)->findOneBy(['externalId' => $guildId]);

        /** @var Entity\Player $player */
        $player = $this->playerRepository->findOneBy(['externalId' => $playerId]);

        if (!$guild || !$player) {
            throw new NotFoundHttpException();
        }

        $playerSnapshots = $player->getSnapshots($guild);
        $playerSnapshots = $playerSnapshots->filter(function (Entity\PlayerSnapshot $snapshot) use ($guild) {
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

        return $this->render('player.html.twig', [
            'player' => $player,
            'xpData' => $xpData,
        ]);
    }
}
