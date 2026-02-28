<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Player;
use Bgl\Domain\Plays\Entities\Players as PlayerRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Player>
 */
final class Players extends DoctrineRepository implements PlayerRepository
{
    #[\Override]
    public function getType(): string
    {
        return Player::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'pl';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
