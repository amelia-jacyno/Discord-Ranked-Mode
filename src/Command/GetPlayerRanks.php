<?php

namespace App\Command;

use App\DTO;
use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\PlayerRanksResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetPlayerRanks extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlayerRepository $playerRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('get_player_ranks')
            ->setDescription('Get player ranks')
            ->addArgument('guild_id', InputArgument::REQUIRED, 'The guild ID to get player ranks for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $guildId = $input->getArgument('guild_id');
        $guild = $this->entityManager->getRepository(Entity\Guild::class)->findOneBy(['externalId' => $guildId]);

        if (!$guild) {
            $output->writeln('Guild not found.');

            return Command::FAILURE;
        }

        $players = $this->playerRepository->getPlayersWithAMonthOfSnapshots($guild);
        $playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($players);
        /** @var array<string, array<DTO\PlayerRankInfo>> $ranks */
        $ranks = [];
        foreach ($playerRankInfos as $playerRankInfo) {
            $ranks[$playerRankInfo->rank->getName()][] = $playerRankInfo;
        }

        foreach ($ranks as $rank => $players) {
            $output->writeln($rank . ' (' . count($players) . ')');
            foreach ($players as $player) {
                $output->writeln('  ' . $player->username . ': ' . round($player->dailyXp, 1) . 'xp');
            }
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
