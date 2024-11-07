<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MakePlayerSnapshotsBelongToGuild extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE player_snapshots
            ADD COLUMN guild_id INT DEFAULT NULL AFTER player_id,
            ADD INDEX guild_id_idx (guild_id),
            ADD CONSTRAINT guild_id_fk FOREIGN KEY (guild_id) REFERENCES guilds (id)
        ;");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE player_snapshots 
            DROP FOREIGN KEY guild_id_fk,
            DROP INDEX guild_id_idx,
            DROP COLUMN guild_id
        ;");
    }
}
