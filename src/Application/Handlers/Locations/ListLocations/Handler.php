<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\ListLocations;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
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

        $userId = new Uuid($query->userId);
        $offset = ($query->page - 1) * $query->size;

        $sortField = $query->sort;
        $sortDir = $query->order;

        $locations = $this->locations->findAllByUser($userId, $query->size, $offset, $sortField, $sortDir);
        $total = $this->locations->countByUser($userId);

        $data = [];
        foreach ($locations as $location) {
            $data[] = [
                'id' => (string)$location->getId(),
                'name' => $location->getName(),
                'address' => $location->getAddress(),
                'notes' => $location->getNotes(),
                'url' => $location->getUrl(),
                'createdAt' => $location->getCreatedAt()->getFormattedValue('c'),
            ];
        }

        return new Result(
            data: $data,
            total: $total,
            page: $query->page,
            size: $query->size,
        );
    }
}
