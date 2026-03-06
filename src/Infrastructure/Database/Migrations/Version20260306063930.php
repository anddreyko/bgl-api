<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306063930 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return '';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plays_player DROP CONSTRAINT fk_plays_player_play_id');
        $this->addSql('ALTER TABLE plays_player ADD CONSTRAINT FK_B9ED8D4F25576DBD FOREIGN KEY (play_id) REFERENCES plays_session (id)');
        $this->addSql('CREATE INDEX idx_plays_player_mate_id ON plays_player (mate_id)');
        $this->addSql('ALTER INDEX idx_plays_player_play_id RENAME TO IDX_B9ED8D4F25576DBD');
        $this->addSql('CREATE INDEX idx_plays_session_game_id ON plays_session (game_id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plays_player DROP CONSTRAINT FK_B9ED8D4F25576DBD');
        $this->addSql('DROP INDEX idx_plays_player_mate_id');
        $this->addSql('ALTER TABLE plays_player ADD CONSTRAINT fk_plays_player_play_id FOREIGN KEY (play_id) REFERENCES plays_session (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_b9ed8d4f25576dbd RENAME TO idx_plays_player_play_id');
        $this->addSql('DROP INDEX idx_plays_session_game_id');
    }
}
