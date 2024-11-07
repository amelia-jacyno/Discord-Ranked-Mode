<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGuildsTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("CREATE TABLE guilds (
            id INT PRIMARY KEY AUTO_INCREMENT NOT NULL, 
            external_id VARCHAR(255) NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            leaderboard_url VARCHAR(255) DEFAULT NULL,
            leaderboard_provider VARCHAR(255) DEFAULT NULL,
            leaderboard_provider_auth_token VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX external_id_uniq (external_id)
        );");
    }
}
