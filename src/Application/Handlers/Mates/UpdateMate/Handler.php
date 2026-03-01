<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\UpdateMate;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;

/**
 * @implements MessageHandler<Result, Command>
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
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Mate|null $mate */
        $mate = $this->mates->find($command->mateId);

        if ($mate === null || $mate->isDeleted() || $mate->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Not Found');
        }

        $name = $command->name;

        $existing = $this->mates->findByUserAndName(new Uuid($command->userId), $name);
        if ($existing !== null && $existing->getId()->getValue() !== $mate->getId()->getValue()) {
            throw new \DomainException('Mate with this name already exists');
        }

        $mate->update($name, $command->notes);

        return new Result(
            id: (string)$mate->getId(),
            name: $mate->getName(),
            notes: $mate->getNotes(),
        );
    }
}
