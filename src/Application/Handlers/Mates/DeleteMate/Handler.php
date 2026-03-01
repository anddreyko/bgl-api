<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\DeleteMate;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates,
        private ClockInterface $clock,
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
            throw new \Bgl\Core\Exceptions\NotFoundException('Mate not found');
        }

        $mate->softDelete(new DateTime($this->clock->now()));

        return new Result(message: 'deleted');
    }
}
