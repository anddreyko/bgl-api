<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\ListMates;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mates;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates,
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

        $mates = $this->mates->findAllByUser($userId, $query->size, $offset, $sortField, $sortDir);
        $total = $this->mates->countByUser($userId);

        $data = [];
        foreach ($mates as $mate) {
            $data[] = [
                'id' => (string)$mate->getId(),
                'name' => $mate->getName(),
                'notes' => $mate->getNotes(),
                'createdAt' => $mate->getCreatedAt()->getFormattedValue('c'),
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
