<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\OpenSession;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
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
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());

        $play = Play::open(
            id: $this->uuidGenerator->generate(),
            userId: new Uuid($command->userId),
            name: $command->name,
            startedAt: $now,
        );

        $this->plays->add($play);

        return new Result(
            sessionId: $play->getId()->getValue() ?? '',
        );
    }
}
