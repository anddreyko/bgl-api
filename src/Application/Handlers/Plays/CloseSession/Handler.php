<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CloseSession;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Plays;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Play|null $play */
        $play = $this->plays->find($command->sessionId);

        if ($play === null) {
            throw new \DomainException('Play not found');
        }

        if ($play->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Access denied');
        }

        $finishedAt = $command->finishedAt
            ?? \DateTimeImmutable::createFromInterface($this->clock->now());

        $play->close($finishedAt);

        return new Result(
            sessionId: $play->getId()->getValue() ?? '',
            startedAt: $play->getStartedAt()->format('c'),
            finishedAt: $finishedAt->format('c'),
        );
    }
}
