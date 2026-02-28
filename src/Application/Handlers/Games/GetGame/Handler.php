<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\GetGame;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Games\Entities\Game;
use Bgl\Domain\Games\Entities\Games;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Games $games,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        /** @var Game|null $game */
        $game = $this->games->find($query->gameId);

        if ($game === null) {
            throw new NotFoundException('Game not found');
        }

        return new Result(
            id: (string)$game->getId(),
            bggId: $game->getBggId(),
            name: $game->getName(),
            yearPublished: $game->getYearPublished(),
        );
    }
}
