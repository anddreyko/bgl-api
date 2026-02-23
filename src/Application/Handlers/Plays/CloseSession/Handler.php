<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CloseSession;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Plays\Entities\Session;
use Bgl\Domain\Plays\Entities\Sessions;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Sessions $sessions,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Session|null $session */
        $session = $this->sessions->find($command->sessionId);

        if ($session === null) {
            throw new \DomainException('Session not found');
        }

        if ($session->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Access denied');
        }

        $finishedAt = $command->finishedAt
            ?? \DateTimeImmutable::createFromInterface($this->clock->now());

        $session->close($finishedAt);

        return new Result(
            sessionId: $session->getId()->getValue() ?? '',
            startedAt: $session->getStartedAt()->format('c'),
            finishedAt: $finishedAt->format('c'),
        );
    }
}
