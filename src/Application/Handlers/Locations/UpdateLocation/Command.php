<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\UpdateLocation;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $userId
     * @param non-empty-string $locationId
     * @param non-empty-string $name
     */
    public function __construct(
        public string $userId,
        public string $locationId,
        public string $name,
        public ?string $address = null,
        public ?string $notes = null,
        public ?string $url = null,
    ) {
    }
}
