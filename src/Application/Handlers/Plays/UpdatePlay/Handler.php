<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Plays\Entities\Visibility;

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
        $play = $this->plays->find($command->sessionId);

        if ($play === null) {
            throw new \DomainException('Play not found');
        }

        if ($play->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Access denied');
        }

        $gameId = $this->resolveGameId($command->gameId);

        $play->update(
            $command->name,
            $gameId,
            Visibility::from($command->visibility),
        );

        return new Result(
            sessionId: (string)$play->getId(),
        );
    }

    private function resolveGameId(?string $gameId): ?Uuid
    {
        if ($gameId === null || $gameId === '') {
            return null;
        }

        $game = $this->games->find($gameId);
        if ($game === null) {
            throw new NotFoundException('Game not found');
        }

        return new Uuid($gameId);
    }
}
