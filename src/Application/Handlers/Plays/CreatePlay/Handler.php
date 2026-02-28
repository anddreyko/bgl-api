<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CreatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Domain\Plays\Entities\Players;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Plays\Entities\Visibility;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Players $players,
        private Mates $mates,
        private Games $games,
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
            players: $this->players,
            gameId: $command->gameId,
            visibility: Visibility::from($command->visibility),
        );

        $this->addPlayers($play, $command->players);

        if ($command->finishedAt !== null) {
            $play->finalize($command->finishedAt);
        }

        $this->plays->add($play);

        return new Result(sessionId: (string)$play->getId());
    }

    /**
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string}> $players
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
            ));
        }
    }

    private function assertGameExists(Uuid $gameId): void
    {
        if ($this->games->find((string)$gameId) === null) {
            throw new NotFoundException('Game not found');
        }
    }

    /**
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string}> $players
     */
    private function validatePlayers(array $players, Uuid $userId): void
    {
        $mateIds = [];
        foreach ($players as $playerData) {
            $mateId = $playerData['mate_id'];

            if (isset($mateIds[$mateId])) {
                throw new \DomainException('Duplicate player: same mate cannot be added twice');
            }
            $mateIds[$mateId] = true;

            $mate = $this->mates->find($mateId);
            if ($mate === null) {
                throw new \DomainException('Mate not found: ' . $mateId);
            }

            if ((string)$mate->getUserId() !== (string)$userId) {
                throw new \DomainException('Mate does not belong to user');
            }

            if ($mate->isDeleted()) {
                throw new \DomainException('Mate is deleted: ' . $mateId);
            }
        }
    }
}
