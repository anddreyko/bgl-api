<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\PlayersFactory;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Domain\Plays\Visibility;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Mates $mates,
        private Games $games,
        private Users $users,
        private PlayersFactory $playersFactory,
        private UuidGenerator $uuidGenerator,
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

        if ($command->players !== []) {
            $this->validatePlayers($command->players, $command->userId);
        }

        $play->update(
            $command->name,
            $command->gameId,
            Visibility::from($command->visibility),
            $command->status !== null ? PlayStatus::from($command->status) : null,
            locationId: $command->locationId,
            notes: $command->notes,
        );

        if ($command->players !== []) {
            $this->replacePlayers($play, $command->players);
        }

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

    /**
     * @return ?array{id: string, name: string}
     */
    private function resolveGame(Play $play): ?array
    {
        $gameId = $play->getGameId();
        if ($gameId === null) {
            return null;
        }

        /** @var Game|null $gameEntity */
        $gameEntity = $this->games->find((string)$gameId);

        return $gameEntity !== null
            ? ['id' => (string)$gameEntity->getId(), 'name' => $gameEntity->getName()]
            : null;
    }

    /**
     * @return list<array{id: string, mate_id: string, score: ?int, is_winner: bool, color: ?string, team_tag: ?string, number: ?int}>
     */
    private function transformPlayers(Play $play): array
    {
        $players = [];
        /** @var Player $player */
        foreach ($play->getPlayers() as $player) {
            $players[] = [
                'id' => (string)$player->getId(),
                'mate_id' => (string)$player->getMateId(),
                'score' => $player->getScore(),
                'is_winner' => $player->isWinner(),
                'color' => $player->getColor(),
                'team_tag' => $player->getTeamTag(),
                'number' => $player->getNumber(),
            ];
        }

        return $players;
    }

    private function transformPlay(Play $play): Result
    {
        return new Result(
            id: (string)$play->getId(),
            author: $this->resolveAuthor($play),
            name: $play->getName(),
            status: $play->getStatus()->value,
            visibility: $play->getVisibility()->value,
            startedAt: $play->getStartedAt()->getNullableFormattedValue('c'),
            finishedAt: $play->getFinishedAt()?->getNullableFormattedValue('c'),
            game: $this->resolveGame($play),
            players: $this->transformPlayers($play),
            notes: $play->getNotes(),
            locationId: $play->getLocationId() !== null ? (string)$play->getLocationId() : null,
        );
    }

    /**
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string, team_tag?: ?string, number?: ?int}> $players
     */
    private function replacePlayers(Play $play, array $players): void
    {
        $newPlayers = $this->playersFactory->createEmpty();
        foreach ($players as $playerData) {
            $newPlayers->add(Player::create(
                id: $this->uuidGenerator->generate(),
                play: $play,
                mateId: new Uuid($playerData['mate_id']),
                score: $playerData['score'] ?? null,
                isWinner: $playerData['is_winner'] ?? false,
                color: $playerData['color'] ?? null,
                teamTag: $playerData['team_tag'] ?? null,
                number: $playerData['number'] ?? null,
            ));
        }
        $play->replacePlayers($newPlayers);
    }

    private function assertGameExists(Uuid $gameId): void
    {
        if ($this->games->find((string)$gameId) === null) {
            throw new NotFoundException('Game not found');
        }
    }

    /**
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string, team_tag?: ?string, number?: ?int}> $players
     */
    private function validatePlayers(array $players, Uuid $userId): void
    {
        $mateIds = [];
        foreach ($players as $playerData) {
            $mateId = $playerData['mate_id'];

            if (isset($mateIds[$mateId])) {
                throw new \Bgl\Domain\Plays\DuplicatePlayerException();
            }
            $mateIds[$mateId] = true;
        }

        /** @var array<string, \Bgl\Domain\Mates\Mate> $matesById */
        $matesById = [];
        foreach ($this->mates->findByIds(array_keys($mateIds)) as $mate) {
            $matesById[(string)$mate->getId()] = $mate;
        }

        foreach (array_keys($mateIds) as $mateId) {
            $mate = $matesById[$mateId] ?? null;
            if ($mate === null) {
                throw new NotFoundException('Mate not found: ' . $mateId);
            }

            if ($mate->getUserId() !== null && (string)$mate->getUserId() !== (string)$userId) {
                throw new \Bgl\Domain\Plays\MateNotOwnedByUserException();
            }

            if ($mate->isDeleted()) {
                throw new NotFoundException('Mate is deleted: ' . $mateId);
            }
        }
    }
}
