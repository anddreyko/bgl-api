<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Player\EmptyPlayers;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\Players;

final class Play
{
    /** @var Players Doctrine replaces with PersistentCollection at hydration */
    private $players;

    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private ?string $name,
        private PlayStatus $status,
        private readonly DateTime $startedAt,
        private ?DateTime $finishedAt,
        private ?Uuid $gameId = null,
        private Visibility $visibility = Visibility::Private,
    ) {
        $this->players = new EmptyPlayers();
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        ?string $name,
        DateTime $startedAt,
        Players $players,
        ?Uuid $gameId = null,
        Visibility $visibility = Visibility::Private,
    ): self {
        $play = new self(
            $id,
            $userId,
            $name,
            PlayStatus::Draft,
            $startedAt,
            null,
            $gameId,
            $visibility,
        );
        $play->players = $players;

        return $play;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStatus(): PlayStatus
    {
        return $this->status;
    }

    public function getStartedAt(): DateTime
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function getGameId(): ?Uuid
    {
        return $this->gameId;
    }

    public function getVisibility(): Visibility
    {
        return $this->visibility;
    }

    public function update(?string $name, ?Uuid $gameId, Visibility $visibility): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new PlayNotDraftException('Play can only be updated in draft status.');
        }

        $this->name = $name;
        $this->gameId = $gameId;
        $this->visibility = $visibility;
    }

    public function changeVisibility(Visibility $visibility): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new PlayNotDraftException('Visibility can only be changed in draft status.');
        }

        $this->visibility = $visibility;
    }

    public function addPlayer(Player $player): void
    {
        $this->players->add($player);
    }

    /** @return Players */
    public function getPlayers()
    {
        return $this->players;
    }


    public function finalize(DateTime $finishedAt): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new PlayNotDraftException('Play can only be finalized from draft status.');
        }

        if ($finishedAt->getValue() <= $this->startedAt->getValue()) {
            throw new FinishedAtBeforeStartedAtException();
        }

        $this->status = PlayStatus::Published;
        $this->finishedAt = $finishedAt;
    }
}
