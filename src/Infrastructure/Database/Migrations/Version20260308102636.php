<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308102636 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Seed system mates (Anonymous, Automa) and self-as-mate for existing user';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $now = new \DateTimeImmutable()->format('Y-m-d H:i:s');

        // System mates (shared by all users, no user_id)
        $this->addSql(
            "INSERT INTO mates_mate (id, user_id, name, notes, deleted_at, created_at)
             VALUES (:id, NULL, :name, NULL, NULL, :created_at)
             ON CONFLICT (id) DO NOTHING",
            [
                'id' => '00000000-0000-4000-a000-000000000001',
                'name' => 'Anonymous',
                'created_at' => $now,
            ],
        );

        $this->addSql(
            "INSERT INTO mates_mate (id, user_id, name, notes, deleted_at, created_at)
             VALUES (:id, NULL, :name, NULL, NULL, :created_at)
             ON CONFLICT (id) DO NOTHING",
            [
                'id' => '00000000-0000-4000-a000-000000000002',
                'name' => 'Automa',
                'created_at' => $now,
            ],
        );

        // Self-as-mate for existing local user
        $this->addSql(
            "INSERT INTO mates_mate (id, user_id, name, notes, deleted_at, created_at)
             VALUES (:id, :user_id, :name, NULL, NULL, :created_at)
             ON CONFLICT DO NOTHING",
            [
                'id' => '521855a8-6c02-4c11-9dfa-88f27c33897b',
                'user_id' => '521855a8-6c02-4c11-9dfa-88f27c33897a',
                'name' => 'anddreyko',
                'created_at' => $now,
            ],
        );
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE FROM mates_mate WHERE id IN (:id1, :id2)",
            [
                'id1' => '00000000-0000-4000-a000-000000000001',
                'id2' => '00000000-0000-4000-a000-000000000002',
            ],
        );

        $this->addSql(
            "DELETE FROM mates_mate WHERE user_id = :user_id AND name = :name",
            [
                'user_id' => '521855a8-6c02-4c11-9dfa-88f27c33897a',
                'name' => 'anddreyko',
            ],
        );
    }
}
