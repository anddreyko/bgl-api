<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230419095420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE auth_user (id UUID NOT NULL, hash VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, email VARCHAR(255) NOT NULL, status VARCHAR(255) CHECK(status IN (\'active\', \'wait\')) NOT NULL, token_value VARCHAR(255) DEFAULT NULL, token_expires DATE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3B536FDE7927C74 ON auth_user (email)');
        $this->addSql('COMMENT ON COLUMN auth_user.id IS \'(DC2Type:id)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.hash IS \'(DC2Type:password_hash)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.email IS \'(DC2Type:email)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.status IS \'(DC2Type:user_status)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.token_expires IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE auth_user');
    }
}
