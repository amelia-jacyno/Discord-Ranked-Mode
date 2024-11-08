<?php

namespace App\DTO;

use Carbon\CarbonInterface;

class PlayerWithSnapshotData
{
    public function __construct(
        public int $id,
        public string $username,
        public string $avatar,
        public string $externalId,
        public int $oldestSnapshotXp,
        public int $oldestSnapshotLevel,
        public CarbonInterface $oldestSnapshotDate,
        public int $newestSnapshotXp,
        public int $newestSnapshotLevel,
        public CarbonInterface $newestSnapshotDate
    ) {
    }
}