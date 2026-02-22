<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Plays;

use Bgl\Domain\Plays\Entities\Session;
use Bgl\Domain\Plays\Entities\Sessions as PlaysSessions;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Session>
 */
final class Sessions extends DoctrineRepository implements PlaysSessions
{
    #[\Override]
    public function getType(): string
    {
        return Session::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 's';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
