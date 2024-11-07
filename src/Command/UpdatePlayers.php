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

        /** @var Entity\Guild[] $guilds */
        $guilds = $this->entityManager->getRepository(Entity\Guild::class)->findAll();
        $guilds = array_filter($guilds, fn (Entity\Guild $guild) => $guild->getLeaderboardUrl() && $guild->getLeaderboardProvider());

        $updatedGuilds = [];
        foreach ($guilds as $guild) {
            $lastUpdate = $this->playerRepository->getLastPlayerSnapshotUpdate($guild);

            // Leave a margin for inconsistencies in cron timings and command execution for multiple guilds
            if (!$force && isset($lastUpdate) && $lastUpdate->floatDiffInHours(Carbon::now()) < 11.5) {
                $output->writeln(sprintf(
                    'Skipping guild %s (%s) as it was updated less than 12 hours ago.',
                    $guild->getName(),
                    $guild->getExternalId()
                ));

                continue;
            }

            $this->updateGuildPlayers($guild, $output);
            $updatedGuilds[] = $guild;
        }

        $output->writeln(sprintf('Updated %d guilds.', count($updatedGuilds)));

        return Command::SUCCESS;
    }

    private function updateGuildPlayers(Entity\Guild $guild, OutputInterface $output): void
    {
        $externalPlayers = LeaderboardProviderResolver::resolveProvider($guild->getLeaderboardProvider())::fetchPlayers();

        foreach ($externalPlayers as $playerData) {
            $player = $this->playerRepository->findOneBy([
                'externalId' => $playerData->id,
            ]);

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
                ->setPlayer($player)
                ->setGuild($guild);
            $player->addSnapshot($snapshot);
        }
        $this->entityManager->flush();

        $output->writeln(sprintf(
            'Updated %d players for guild %s (%s)',
            count($externalPlayers),
            $guild->getName(),
            $guild->getExternalId()
        ));
    }
}
