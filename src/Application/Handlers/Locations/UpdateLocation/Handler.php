<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\UpdateLocation;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\LocationAlreadyExistsException;
use Bgl\Domain\Locations\Locations;

/**
 * @implements MessageHandler<Result, Command>
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
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Location|null $location */
        $location = $this->locations->find($command->locationId);

        if ($location === null || $location->isDeleted() || $location->getUserId()->getValue() !== $command->userId) {
            throw new \Bgl\Core\Exceptions\NotFoundException('Location not found');
        }

        $name = $command->name;

        $existing = $this->locations->findByUserAndName(new Uuid($command->userId), $name);
        if ($existing !== null && $existing->getId()->getValue() !== $location->getId()->getValue()) {
            throw new LocationAlreadyExistsException();
        }

        $location->update($name, $command->address, $command->notes, $command->url);

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
