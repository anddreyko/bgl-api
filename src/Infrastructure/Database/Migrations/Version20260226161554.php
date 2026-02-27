<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226161554 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add mates_mate and games_game tables';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE games_game (id UUID NOT NULL, bgg_id INT NOT NULL, name VARCHAR(255) NOT NULL, year_published INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3C53FD55E01D7737 ON games_game (bgg_id)');
        $this->addSql('CREATE TABLE mates_mate (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(100) NOT NULL, notes TEXT DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_MATES_USER_NAME ON mates_mate (user_id, name)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_MATES_USER_NAME');
        $this->addSql('DROP TABLE mates_mate');
        $this->addSql('DROP TABLE games_game');
    }
}
