<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\CreateLocation;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\LocationAlreadyExistsException;
use Bgl\Domain\Locations\Locations;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Locations $locations,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $userId = new Uuid($command->userId);
        $name = $command->name;

        $existing = $this->locations->findByUserAndName($userId, $name);
        if ($existing !== null) {
            throw new LocationAlreadyExistsException();
        }

        $now = new DateTime($this->clock->now());

        $location = Location::create(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            name: $name,
            address: $command->address,
            notes: $command->notes,
            url: $command->url,
            createdAt: $now,
        );

        $this->locations->add($location);

        return new Result(
            id: (string)$location->getId(),
            name: $location->getName(),
            address: $location->getAddress(),
            notes: $location->getNotes(),
            url: $location->getUrl(),
            createdAt: $now->getFormattedValue('c'),
        );
    }
}
