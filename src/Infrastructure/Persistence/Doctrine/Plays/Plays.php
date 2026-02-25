<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Plays;

use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Plays as DomainPlays;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Play>
 */
final class Plays extends DoctrineRepository implements DomainPlays
{
    #[\Override]
    public function getType(): string
    {
        return Play::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'p';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
