<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Plays as PlayRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Play>
 */
final class Plays extends DoctrineRepository implements PlayRepository
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
