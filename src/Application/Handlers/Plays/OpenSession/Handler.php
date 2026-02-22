<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\OpenSession;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Session;
use Bgl\Domain\Plays\Entities\Sessions;
use Psr\Clock\ClockInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;

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

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());

        $session = Session::open(
            id: new Uuid(RamseyUuid::uuid4()->toString()),
            userId: $command->userId,
            name: $command->name,
            startedAt: $now,
        );

        $this->sessions->add($session);

        return new Result(
            sessionId: $session->getId()->getValue() ?? '',
        );
    }
}
