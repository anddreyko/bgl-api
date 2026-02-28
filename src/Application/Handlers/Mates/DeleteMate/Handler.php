<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\DeleteMate;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Mates\Entities\Mate;
use Bgl\Domain\Mates\Entities\Mates;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<null, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): mixed
    {
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Mate|null $mate */
        $mate = $this->mates->find($command->mateId);

        if ($mate === null || $mate->isDeleted() || $mate->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Not Found');
        }

        $mate->softDelete(new DateTime($this->clock->now()));

        return null;
    }
}
