<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306063820 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Drop cross-context FKs and unused auth_user.version column';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE plays_session DROP CONSTRAINT fk_plays_session_game_id');
        $this->addSql('ALTER TABLE plays_player DROP CONSTRAINT fk_plays_player_mate_id');
        $this->addSql('ALTER TABLE auth_user DROP COLUMN version');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_user ADD COLUMN version INT DEFAULT 1 NOT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE plays_session
                ADD CONSTRAINT fk_plays_session_game_id FOREIGN KEY (game_id) REFERENCES games_game (id)
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plays_player
                ADD CONSTRAINT fk_plays_player_mate_id FOREIGN KEY (mate_id) REFERENCES mates_mate (id)
            SQL);
    }
}
