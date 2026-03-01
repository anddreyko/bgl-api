<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\FinalizePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;
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
        $play = $this->plays->find((string)$command->sessionId);

        if ($play === null) {
            throw new NotFoundException('Play not found');
        }

        if ((string)$play->getUserId() !== (string)$command->userId) {
            throw new \DomainException('Access denied');
        }

        $finishedAt = $command->finishedAt ?? new DateTime($this->clock->now());

        $play->finalize($finishedAt);

        return new Result(
            sessionId: (string)$play->getId(),
            startedAt: $play->getStartedAt()->getFormattedValue('c'),
            finishedAt: $finishedAt->getFormattedValue('c'),
        );
    }
}
