<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\GetMate;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Mates\Mate;
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

        /** @var Mate|null $mate */
        $mate = $this->mates->find($query->mateId);

        if ($mate === null || $mate->isDeleted() || $mate->getUserId()->getValue() !== $query->userId) {
            throw new \DomainException('Not Found');
        }

        return new Result(
            id: (string)$mate->getId(),
            name: $mate->getName(),
            notes: $mate->getNotes(),
            createdAt: $mate->getCreatedAt()->getFormattedValue('c'),
        );
    }
}
