<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241002093712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add time zone to Sessions table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE records_session
    ALTER COLUMN started_at TYPE timestamp(0) WITH TIME ZONE USING started_at::timestamp(0) WITH TIME ZONE"
        );
        $this->addSql(
            "ALTER TABLE records_session
    ALTER COLUMN finished_at TYPE timestamp(0) WITH TIME ZONE USING finished_at::timestamp(0) WITH TIME ZONE"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE records_session
    ALTER COLUMN started_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING started_at::timestamp(0) WITHOUT TIME ZONE"
        );
        $this->addSql(
            "ALTER TABLE records_session
    ALTER COLUMN finished_at TYPE timestamp(0) WITHOUT TIME ZONE USING finished_at::timestamp(0) WITHOUT TIME ZONE"
        );
    }
}
