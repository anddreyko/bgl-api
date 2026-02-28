<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CreatePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
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

        $userId = new Uuid($command->userId);

        if ($command->players !== []) {
            $this->validatePlayers($command->players, $userId);
        }

        $gameId = $this->resolveGameId($command->gameId);

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        $startedAt = $this->resolveDateTime($command->startedAt, 'startedAt') ?? $now;
        $finishedAt = $this->resolveDateTime($command->finishedAt, 'finishedAt');

        $play = Play::create(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            name: $command->name,
            startedAt: $startedAt,
            gameId: $gameId,
            visibility: Visibility::from($command->visibility),
        );

        foreach ($command->players as $p) {
            $play->addPlayer(Player::create(
                id: $this->uuidGenerator->generate(),
                play: $play,
                mateId: new Uuid($p['mate_id']),
                score: $p['score'] ?? null,
                isWinner: $p['is_winner'] ?? false,
                color: $p['color'] ?? null,
            ));
        }

        if ($finishedAt !== null) {
            $play->close($finishedAt);
        }

        $this->plays->add($play);

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

    private function resolveDateTime(?string $value, string $field): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\DateMalformedStringException) {
            throw new \DomainException("Invalid {$field} datetime format");
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
