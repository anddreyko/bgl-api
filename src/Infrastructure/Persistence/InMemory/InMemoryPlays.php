<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;

/**
 * @extends InMemoryRepository<Play>
 */
final class InMemoryPlays extends InMemoryRepository implements Plays
{
    public function __construct()
    {
        parent::__construct(new AnyFieldAccessor());
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
