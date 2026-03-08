<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\DeleteLocation;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<null, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Locations $locations,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): null
    {
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Location|null $location */
        $location = $this->locations->find($command->locationId);

        if ($location === null || $location->isDeleted() || $location->getUserId()->getValue() !== $command->userId) {
            throw new \Bgl\Core\Exceptions\NotFoundException('Location not found');
        }

        $location->softDelete(new DateTime($this->clock->now()));

        return null;
    }
}
