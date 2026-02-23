<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223091859 extends AbstractMigration
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
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->addSql('CREATE TABLE auth_email_confirmation_token (id UUID NOT NULL, user_id UUID NOT NULL, token VARCHAR(255) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D70C32755F37A13B ON auth_email_confirmation_token (token)');
        $this->addSql('CREATE TABLE plays_session (id UUID NOT NULL, user_id UUID NOT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        // phpcs:enable Generic.Files.LineLength.TooLong
        $this->addSql('ALTER TABLE auth_user ADD password_hash VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE auth_user ADD token_version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE auth_user ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE auth_email_confirmation_token');
        $this->addSql('DROP TABLE plays_session');
        $this->addSql('ALTER TABLE auth_user DROP password_hash');
        $this->addSql('ALTER TABLE auth_user DROP token_version');
        $this->addSql('ALTER TABLE auth_user ALTER created_at TYPE DATE');
    }
}
