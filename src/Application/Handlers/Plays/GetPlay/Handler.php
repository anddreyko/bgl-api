<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\GetPlay;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Domain\Plays\Visibility;

/**
 * @implements MessageHandler<Result, Query>
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
        /** @var Query $query */
        $query = $envelope->message;

        /** @var Play|null $play */
        $play = $this->plays->find($query->playId);

        if ($play === null) {
            throw new NotFoundException('Session not found');
        }

        $this->checkAccess($play, $query->userId);

        return $this->transformPlay($play);
    }

    private function checkAccess(Play $play, ?string $userId): void
    {
        $isOwner = $userId !== null && (string)$play->getUserId() === $userId;

        if ($play->getStatus() === PlayStatus::Draft && !$isOwner) {
            throw new NotFoundException('Session not found');
        }

        match ($play->getVisibility()) {
            Visibility::Private => $isOwner ? null : throw new NotFoundException('Session not found'),
            Visibility::Link, Visibility::Public => null,
            Visibility::Authenticated => $userId !== null ? null : throw new AuthenticationException('Unauthorized'),
            // TODO: MATES-002 -- full participants check after mate-to-user linking
            Visibility::Participants => $isOwner ? null : throw new NotFoundException('Session not found'),
        };
    }

    private function transformPlay(Play $play): Result
    {
        $game = null;
        $gameId = $play->getGameId();
        if ($gameId !== null) {
            /** @var Game|null $gameEntity */
            $gameEntity = $this->games->find((string)$gameId);
            if ($gameEntity !== null) {
                $game = [
                    'id' => (string)$gameEntity->getId(),
                    'name' => $gameEntity->getName(),
                ];
            }
        }

        $players = [];
        /** @var Player $player */
        foreach ($play->getPlayers() as $player) {
            $players[] = [
                'id' => (string)$player->getId(),
                'mate_id' => (string)$player->getMateId(),
                'score' => $player->getScore(),
                'is_winner' => $player->isWinner(),
                'color' => $player->getColor(),
            ];
        }

        return new Result(
            id: (string)$play->getId(),
            name: $play->getName(),
            status: $play->getStatus()->value,
            visibility: $play->getVisibility()->value,
            startedAt: $play->getStartedAt()->getNullableFormattedValue('c'),
            finishedAt: $play->getFinishedAt()?->getNullableFormattedValue('c'),
            game: $game,
            players: $players,
        );
    }
}
