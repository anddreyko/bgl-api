<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240626202912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Sessions table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE records_session (id UUID NOT NULL, status INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('COMMENT ON COLUMN records_session.id IS \'(DC2Type:session_id)\'');
        $this->addSql('COMMENT ON COLUMN records_session.status IS \'(DC2Type:session_status)\'');
        $this->addSql('COMMENT ON COLUMN records_session.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN records_session.finished_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE records_session');
    }
}
