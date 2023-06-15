<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616112614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change primary key for `auth_user_confirm` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auth_user_confirm ALTER token_value SET NOT NULL');
        $this->addSql('ALTER TABLE auth_user_confirm DROP CONSTRAINT auth_user_confirm_pkey');
        $this->addSql(
            'ALTER TABLE auth_user_confirm ADD CONSTRAINT auth_user_confirm_pkey PRIMARY KEY (user_id, token_value)'
        );

        $this->addSql('CREATE INDEX IDX_29C6B44AA76ED395 ON auth_user_confirm (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_29C6B44AA76ED395');

        $this->addSql('ALTER TABLE auth_user_confirm ALTER token_value SET DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_user_confirm DROP CONSTRAINT auth_user_confirm_pkey');
        $this->addSql('ALTER TABLE auth_user_confirm ADD CONSTRAINT auth_user_confirm_pkey PRIMARY KEY (user_id)');
    }
}
