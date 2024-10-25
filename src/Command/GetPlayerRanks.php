<?php

namespace App\Command;

use App\Repository\PlayerRepository;
use App\Service\PlayerRanksResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\DTO;

final class GetPlayerRanks extends Command
{
    public function __construct(
        private readonly PlayerRepository $playerRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('get_player_ranks')
            ->setDescription('Get player ranks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $players = $this->playerRepository->getPlayersWithAMonthOfSnapshots();
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