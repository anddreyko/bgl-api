<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260222194722 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Create auth_user table';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $this->addSql('CREATE TABLE auth_user (id UUID NOT NULL, email VARCHAR(255) NOT NULL, created_at DATE NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3B536FDE7927C74 ON auth_user (email)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE auth_user');
    }
}
