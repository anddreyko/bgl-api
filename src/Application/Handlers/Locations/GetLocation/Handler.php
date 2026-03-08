<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\GetLocation;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Locations $locations,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        /** @var Location|null $location */
        $location = $this->locations->find($query->locationId);

        if ($location === null || $location->isDeleted() || $location->getUserId()->getValue() !== $query->userId) {
            throw new \Bgl\Core\Exceptions\NotFoundException('Location not found');
        }

        return new Result(
            id: (string)$location->getId(),
            name: $location->getName(),
            address: $location->getAddress(),
            notes: $location->getNotes(),
            url: $location->getUrl(),
            createdAt: $location->getCreatedAt()->getFormattedValue('c'),
        );
    }
}
