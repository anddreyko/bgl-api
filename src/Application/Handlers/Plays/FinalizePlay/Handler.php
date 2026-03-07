<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\FinalizePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Plays;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Games $games,
        private Users $users,
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
            throw new \Bgl\Domain\Plays\PlayAccessDeniedException();
        }

        $finishedAt = $command->finishedAt ?? new DateTime($this->clock->now());

        $play->finalize($finishedAt);

        return $this->transformPlay($play);
    }

    /**
     * @return array{id: string, name: string}
     */
    private function resolveAuthor(Play $play): array
    {
        $author = ['id' => (string)$play->getUserId(), 'name' => ''];
        $user = $this->users->find((string)$play->getUserId());
        if ($user !== null) {
            $author['name'] = $user->getName();
        }

        return $author;
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
            author: $this->resolveAuthor($play),
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
