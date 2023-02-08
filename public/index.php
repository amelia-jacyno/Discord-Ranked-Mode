<?php
require_once '../bootstrap.php';

use App\DTO;
use App\Repository\PlayerRepository;
use App\Service\EntityManagerProvider;
use App\Service\PlayerRanksResolver;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();

$playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($players);
/** @var array<string, array<DTO\PlayerRankInfo>> $ranks */
$ranks = [];
foreach ($playerRankInfos as $playerRankInfo) {
	$ranks[$playerRankInfo->rank->getName()][] = $playerRankInfo;
}
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"
            integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <title>Discord Ranked Mode</title>
</head>
<body>
<div class="container py-3">
	<?php
    $index = 1;
foreach ($ranks as $rank => $players) {
    ?>
        <h3 class="text-center"><?php echo $rank; ?></h3>
        <table class="table table-hover table-fixed mb-4" style="table-layout: fixed">
            <thead>
            <tr>
                <th scope="col" colspan="1">#</th>
                <th scope="col" colspan="3">Name</th>
                <th scope="col" colspan="1">Daily XP</th>
            </tr>
            </thead>
            <tbody>
			<?php
        foreach ($players as $player) {
            $dailyXp = round($player->dailyXp, 1);
            echo "
                        <tr>
                            <td colspan='1'>$index</td>
                            <td colspan='3'>$player->username</td>
                            <td colspan='1'>$dailyXp XP</td>
                        </tr>
                        ";
            ++$index;
        }
    ?>
            </tbody>
        </table>
		<?php
}
?>
</div>
</body>
</html>