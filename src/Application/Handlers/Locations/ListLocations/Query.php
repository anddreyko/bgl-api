<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\ListLocations;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    /**
     * @param non-empty-string $userId
     */
    public function __construct(
        public string $userId,
        public int $page = 1,
        public int $size = 20,
        public string $sort = 'name',
        public string $order = 'asc',
    ) {
    }
}
