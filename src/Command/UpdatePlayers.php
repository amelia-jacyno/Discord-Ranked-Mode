<?php

namespace App\Command;

use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdatePlayers extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlayerRepository $playerRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('update_players')
            ->setDescription('Update players and add new Snapshots from the API')
            ->addOption('force', 'f', null, 'Force update regardless of how recently the players were updated');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        if ($force) {
            $output->writeln('Force update enabled.');
        }

        if (!$force) {
            $lastUpdate = $this->playerRepository->getLastPlayerSnapshotUpdate();
            // Leave a small margin for inconsistencies in cron timings and command execution
            if (isset($lastUpdate) && $lastUpdate->floatDiffInHours(Carbon::now()) < 11.9) {
                $output->writeln('Players already updated less than 12 hours ago.');

                return Command::SUCCESS;
            }
        }

        $externalPlayers = LeaderboardProviderResolver::resolveProvider($_ENV['LEADERBOARD_PROVIDER'] ?? 'mee6')::fetchPlayers();

        foreach ($externalPlayers as $playerData) {
            $player = $this->playerRepository->findOneBy(['externalId' => $playerData->id]);

            if (null === $player) {
                $player = (new Entity\Player())
                    ->setExternalId($playerData->id);
                $this->entityManager->persist($player);
            }

            $player
                ->setUsername($playerData->username)
                ->setAvatar($playerData->avatarUrl);

            $snapshot = (new Entity\PlayerSnapshot())
                ->setLevel($playerData->level)
                ->setXp($playerData->xp)
                ->setMessageCount($playerData->messageCount ?? 0)
                ->setPlayer($player);
            $player->addSnapshot($snapshot);
        }
        $this->entityManager->flush();
        $output->writeln('Players updated successfully.');

        return Command::SUCCESS;
    }
}
