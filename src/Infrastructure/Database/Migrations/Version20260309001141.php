<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate PlayStatus to PlayLifecycle: draft/published -> finished, deleted stays.
 * Column name stays `status` for backward compatibility (fieldName=lifecycle, columnName=status).
 */
final class Version20260309001141 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Convert play status values: draft->current, published->finished';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE plays_session SET status = 'finished' WHERE status = 'published'");
        $this->addSql("UPDATE plays_session SET status = 'current' WHERE status = 'draft'");
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE plays_session SET status = 'draft' WHERE status = 'current'");
        $this->addSql("UPDATE plays_session SET status = 'published' WHERE status = 'finished'");
    }
}
