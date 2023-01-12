<?php
require_once '../bootstrap.php';

use App\Repository\PlayerRepository;
use App\Service\EntityManagerProvider;
use App\Service\PlayerRanksResolver;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();

$ranks = PlayerRanksResolver::resolvePlayerRanks($players);
?>

<html lang="en">
<head>
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
<div class="container">
    <table class="table">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Rank</th>
            <th scope="col">Name</th>
        </tr>
        </thead>
        <tbody>
		<?php
		$index = 1;
		foreach ($ranks as $rank => $players) {
			foreach ($players as $player) {
				echo "
                    <tr>
                        <th scope=\"row\">$index</th>
                        <td>$rank</td>
                        <td>{$player->getUsername()}</td>
                    </tr>
                    ";
				$index++;
			}
		}
		?>
        </tbody>
    </table>
</div>
</body>
</html>