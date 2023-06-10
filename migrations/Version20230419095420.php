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
            'CREATE TABLE auth_user (id UUID NOT NULL, email VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, status VARCHAR(255) CHECK(status IN (\'active\', \'wait\')) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3B536FDE7927C74 ON auth_user (email)');
        $this->addSql('COMMENT ON COLUMN auth_user.id IS \'(DC2Type:id)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.email IS \'(DC2Type:email)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.hash IS \'(DC2Type:password_hash)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.status IS \'(DC2Type:user_status)\'');
        $this->addSql('COMMENT ON COLUMN auth_user.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE auth_user_access (token TEXT NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(user_id, token))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF2942F15F37A13B ON auth_user_access (token)');
        $this->addSql('CREATE INDEX IDX_AF2942F1A76ED395 ON auth_user_access (user_id)');
        $this->addSql('COMMENT ON COLUMN auth_user_access.token IS \'(DC2Type:web_token)\'');
        $this->addSql('COMMENT ON COLUMN auth_user_access.user_id IS \'(DC2Type:id)\'');
        $this->addSql('COMMENT ON COLUMN auth_user_access.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE auth_user_confirm (user_id UUID NOT NULL, token_value VARCHAR(255) DEFAULT NULL, token_expires TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(user_id))'
        );
        $this->addSql('COMMENT ON COLUMN auth_user_confirm.user_id IS \'(DC2Type:id)\'');
        $this->addSql('COMMENT ON COLUMN auth_user_confirm.token_expires IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'ALTER TABLE auth_user_access ADD CONSTRAINT FK_AF2942F1A76ED395 FOREIGN KEY (user_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE auth_user_confirm ADD CONSTRAINT FK_29C6B44AA76ED395 FOREIGN KEY (user_id) REFERENCES auth_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE auth_user_access DROP CONSTRAINT FK_AF2942F1A76ED395');
        $this->addSql('ALTER TABLE auth_user_confirm DROP CONSTRAINT FK_29C6B44AA76ED395');
        $this->addSql('DROP TABLE auth_user');
        $this->addSql('DROP TABLE auth_user_access');
        $this->addSql('DROP TABLE auth_user_confirm');
    }
}
