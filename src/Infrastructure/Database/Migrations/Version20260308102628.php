<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308102628 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Make mates_mate.user_id nullable for system mates';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mates_mate ALTER user_id DROP NOT NULL');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mates_mate ALTER user_id SET NOT NULL');
    }
}
