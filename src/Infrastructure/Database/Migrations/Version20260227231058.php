<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227231058 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add plays_player table and extend plays_session with game_id and visibility';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE plays_player (id UUID NOT NULL, play_id UUID NOT NULL, mate_id UUID NOT NULL, score INT DEFAULT NULL, is_winner BOOLEAN DEFAULT false NOT NULL, color VARCHAR(50) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_plays_player_play_id ON plays_player (play_id)');
        $this->addSql('ALTER TABLE plays_player ADD CONSTRAINT FK_plays_player_play_id FOREIGN KEY (play_id) REFERENCES plays_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plays_player ADD CONSTRAINT FK_plays_player_mate_id FOREIGN KEY (mate_id) REFERENCES mates_mate (id)');
        $this->addSql('ALTER TABLE plays_session ADD game_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE plays_session ADD visibility VARCHAR(255) DEFAULT \'private\' NOT NULL');
        $this->addSql('ALTER TABLE plays_session ADD CONSTRAINT FK_plays_session_game_id FOREIGN KEY (game_id) REFERENCES games_game (id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plays_session DROP CONSTRAINT IF EXISTS FK_plays_session_game_id');
        $this->addSql('DROP TABLE plays_player');
        $this->addSql('ALTER TABLE plays_session DROP game_id');
        $this->addSql('ALTER TABLE plays_session DROP visibility');
    }
}
