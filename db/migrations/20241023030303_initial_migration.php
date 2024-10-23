<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialMigration extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("CREATE TABLE players (
            id INT PRIMARY KEY AUTO_INCREMENT NOT NULL, 
            username VARCHAR(255) NOT NULL, 
            external_id VARCHAR(255) NOT NULL, 
            avatar VARCHAR(255) DEFAULT NULL, 
            UNIQUE INDEX external_id_uniq (external_id), 
            INDEX external_id_idx (external_id)
        );");

        $this->execute("CREATE TABLE player_snapshots (
            id INT AUTO_INCREMENT NOT NULL, 
            player_id INT DEFAULT NULL, 
            xp INT NOT NULL, 
            level INT NOT NULL, 
            message_count INT DEFAULT NULL, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_ED9D730499E6F5DF (player_id), PRIMARY KEY(id),
            CONSTRAINT player_id_fk FOREIGN KEY (player_id) REFERENCES players (id)
        );");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE player_snapshots;");
        $this->execute("DROP TABLE players;");
    }
}
