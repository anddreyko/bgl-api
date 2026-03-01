<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260301083612 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Rename visibility enum values: friends->participants, registered->authenticated';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE plays_session SET visibility = 'participants' WHERE visibility = 'friends'");
        $this->addSql("UPDATE plays_session SET visibility = 'authenticated' WHERE visibility = 'registered'");
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE plays_session SET visibility = 'friends' WHERE visibility = 'participants'");
        $this->addSql("UPDATE plays_session SET visibility = 'registered' WHERE visibility = 'authenticated'");
    }
}
