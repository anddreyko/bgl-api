<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\UpdateLocation;

final readonly class Result
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $address,
        public ?string $notes,
        public ?string $url,
        public string $createdAt,
    ) {
    }
}
