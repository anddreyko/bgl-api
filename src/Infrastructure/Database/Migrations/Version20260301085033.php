<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301085033 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add indexes on plays_session(user_id, started_at) and mates_mate(deleted_at)';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_plays_session_user_id ON plays_session (user_id)');
        $this->addSql('CREATE INDEX IDX_plays_session_started_at ON plays_session (started_at)');
        $this->addSql('CREATE INDEX IDX_mates_mate_deleted_at ON mates_mate (deleted_at)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_plays_session_user_id');
        $this->addSql('DROP INDEX IDX_plays_session_started_at');
        $this->addSql('DROP INDEX IDX_mates_mate_deleted_at');
    }
}
