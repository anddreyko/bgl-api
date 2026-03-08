<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308101235 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add locations table, location_id/notes to plays, team_tag/number to players';
    }

    #[\Override]
    public function up(Schema $schema): void
    {

        $this->addSql('CREATE TABLE locations_location (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, url VARCHAR(500) DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_locations_user_id ON locations_location (user_id)');
        $this->addSql('CREATE INDEX idx_locations_deleted_at ON locations_location (deleted_at)');
        $this->addSql('ALTER TABLE plays_player ADD team_tag VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE plays_player ADD number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plays_session ADD location_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE plays_session ADD notes TEXT DEFAULT NULL');
    }

    #[\Override]
    public function down(Schema $schema): void
    {

        $this->addSql('DROP TABLE locations_location');
        $this->addSql('ALTER TABLE plays_player DROP team_tag');
        $this->addSql('ALTER TABLE plays_player DROP number');
        $this->addSql('ALTER TABLE plays_session DROP location_id');
        $this->addSql('ALTER TABLE plays_session DROP notes');
    }
}
