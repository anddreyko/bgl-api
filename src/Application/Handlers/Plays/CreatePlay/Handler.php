<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CreatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\PlayersFactory;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\Visibility;
use Bgl\Domain\Profile\Users;
use Psr\Clock\ClockInterface;

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
        private Locations $locations,
        private PlayersFactory $playersFactory,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        if ($command->players !== []) {
            $this->validatePlayers($command->players, $command->userId);
        }

        if ($command->gameId !== null) {
            $this->assertGameExists($command->gameId);
        }

        $play = Play::create(
            id: $this->uuidGenerator->generate(),
            userId: $command->userId,
            name: $command->name,
            startedAt: $command->startedAt ?? new DateTime($this->clock->now()),
            players: $this->playersFactory->createEmpty(),
            gameId: $command->gameId,
            visibility: Visibility::from($command->visibility),
            locationId: $command->locationId,
            notes: $command->notes,
        );

        $this->addPlayers($play, $command->players);

        if ($command->finishedAt !== null) {
            $play->finalize($command->finishedAt);
        }

        $this->plays->add($play);

        return $this->transformPlay($play);
    }

    /**
     * @param list<array{
     *     mate_id: non-empty-string,
     *     score?: ?int,
     *     is_winner?: ?bool,
     *     color?: ?string,
     *     team_tag?: ?string,
     *     number?: ?int
     * }> $players
     */
    private function addPlayers(Play $play, array $players): void
    {
        foreach ($players as $player) {
            $play->addPlayer(Player::create(
                id: $this->uuidGenerator->generate(),
                play: $play,
                mateId: new Uuid($player['mate_id']),
                score: $player['score'] ?? null,
                isWinner: $player['is_winner'] ?? false,
                color: $player['color'] ?? null,
                teamTag: $player['team_tag'] ?? null,
                number: $player['number'] ?? null,
            ));
        }
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
     * @return list<array{
     *     id: string,
     *     mate: array{id: string, name: string},
     *     score: ?int,
     *     is_winner: bool,
     *     color: ?string,
     *     team_tag: ?string,
     *     number: ?int
     * }>
     */
    private function transformPlayers(Play $play): array
    {
        $players = [];
        /** @var Player $player */
        foreach ($play->getPlayers() as $player) {
            $mateId = (string)$player->getMateId();
            /** @var Mate|null $mate */
            $mate = $this->mates->find($mateId);

            $players[] = [
                'id' => (string)$player->getId(),
                'mate' => ['id' => $mateId, 'name' => $mate !== null ? $mate->getName() : ''],
                'score' => $player->getScore(),
                'is_winner' => $player->isWinner(),
                'color' => $player->getColor(),
                'team_tag' => $player->getTeamTag(),
                'number' => $player->getNumber(),
            ];
        }

        return $players;
    }

    /**
     * @return ?array{id: string, name: string}
     */
    private function resolveLocation(Play $play): ?array
    {
        $locationId = $play->getLocationId();
        if ($locationId === null) {
            return null;
        }

        /** @var Location|null $location */
        $location = $this->locations->find((string)$locationId);

        return $location !== null
            ? ['id' => (string)$location->getId(), 'name' => $location->getName()]
            : null;
    }

    private function transformPlay(Play $play): Result
    {
        return new Result(
            id: (string)$play->getId(),
            author: $this->resolveAuthor($play),
            name: $play->getName(),
            visibility: $play->getVisibility()->value,
            startedAt: $play->getStartedAt()->getNullableFormattedValue('c'),
            finishedAt: $play->getFinishedAt()?->getNullableFormattedValue('c'),
            game: $this->resolveGame($play),
            players: $this->transformPlayers($play),
            status: $play->getLifecycle()->value,
            notes: $play->getNotes(),
            location: $this->resolveLocation($play),
        );
    }

    private function assertGameExists(Uuid $gameId): void
    {
        if ($this->games->find((string)$gameId) === null) {
            throw new NotFoundException('Game not found');
        }
    }

    /**
     * @param list<array{
     *     mate_id: non-empty-string,
     *     score?: ?int,
     *     is_winner?: ?bool,
     *     color?: ?string,
     *     team_tag?: ?string,
     *     number?: ?int
     * }> $players
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
