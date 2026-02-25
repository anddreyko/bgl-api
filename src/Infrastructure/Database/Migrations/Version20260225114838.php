<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225114838 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return '';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE auth_passkey (
                id UUID NOT NULL,
                user_id UUID NOT NULL,
                credential_id VARCHAR(255) NOT NULL,
                credential_data TEXT NOT NULL,
                counter INT DEFAULT 0 NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                label VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_4EA1148F2558A7A5 ON auth_passkey (credential_id)',
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE auth_passkey_challenge (
                id UUID NOT NULL,
                challenge VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                user_id UUID DEFAULT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_E64808BD7098951 ON auth_passkey_challenge (challenge)',
        );
        $this->addSql('ALTER TABLE auth_user ALTER email TYPE VARCHAR');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE auth_passkey');
        $this->addSql('DROP TABLE auth_passkey_challenge');
        $this->addSql('ALTER TABLE auth_user ALTER email TYPE VARCHAR(255)');
    }
}
