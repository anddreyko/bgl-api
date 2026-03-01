<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\Visibility;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Games $games,
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
            throw new \Bgl\Domain\Plays\PlayAccessDeniedException();
        }

        if ($command->gameId !== null) {
            $this->assertGameExists($command->gameId);
        }

        $play->update(
            $command->name,
            $command->gameId,
            Visibility::from($command->visibility),
        );

        return new Result(
            sessionId: (string)$play->getId(),
        );
    }

    private function assertGameExists(Uuid $gameId): void
    {
        if ($this->games->find((string)$gameId) === null) {
            throw new NotFoundException('Game not found');
        }
    }
}
