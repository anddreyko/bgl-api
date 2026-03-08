<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\GetLocation;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    /**
     * @param non-empty-string $userId
     * @param non-empty-string $locationId
     */
    public function __construct(
        public string $userId,
        public string $locationId,
    ) {
    }
}
