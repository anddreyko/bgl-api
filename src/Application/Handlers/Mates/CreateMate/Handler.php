<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\CreateMate;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Entities\Mate;
use Bgl\Domain\Mates\Entities\Mates;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates,
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

        $existing = $this->mates->findByUserAndName($userId, $name);
        if ($existing !== null) {
            throw new \DomainException('Mate with this name already exists');
        }

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());

        $mate = Mate::create(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            name: $name,
            notes: $command->notes,
            createdAt: $now,
        );

        $this->mates->add($mate);

        return new Result(
            id: (string)$mate->getId(),
            name: $mate->getName(),
            notes: $mate->getNotes(),
            createdAt: $now->format('c'),
        );
    }
}
