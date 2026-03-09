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
        private PlayLifecycle $lifecycle,
        private readonly DateTime $startedAt,
        private ?DateTime $finishedAt,
        private ?Uuid $gameId = null,
        private Visibility $visibility = Visibility::Private,
        private ?Uuid $locationId = null,
        private ?string $notes = null,
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
        ?Uuid $locationId = null,
        ?string $notes = null,
    ): self {
        $play = new self(
            $id,
            $userId,
            $name,
            PlayLifecycle::Current,
            $startedAt,
            null,
            $gameId,
            $visibility,
            $locationId,
            $notes,
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

    public function getLifecycle(): PlayLifecycle
    {
        return $this->lifecycle;
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

    public function getLocationId(): ?Uuid
    {
        return $this->locationId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function delete(): void
    {
        if ($this->lifecycle === PlayLifecycle::Deleted) {
            throw new PlayDeletedException('Play is already deleted.');
        }

        $this->lifecycle = PlayLifecycle::Deleted;
    }

    public function restore(): void
    {
        if ($this->lifecycle !== PlayLifecycle::Deleted) {
            throw new PlayNotDeletedException('Only deleted plays can be restored.');
        }

        $this->lifecycle = PlayLifecycle::Finished;
    }

    public function update(
        ?string $name,
        ?Uuid $gameId,
        Visibility $visibility,
        ?Uuid $locationId = null,
        ?string $notes = null,
    ): void {
        if ($this->lifecycle === PlayLifecycle::Deleted) {
            throw new PlayDeletedException('Deleted play cannot be updated.');
        }

        $this->name = $name;
        $this->gameId = $gameId;
        $this->visibility = $visibility;
        $this->locationId = $locationId;
        $this->notes = $notes;
    }

    public function addPlayer(Player $player): void
    {
        if ($this->lifecycle === PlayLifecycle::Deleted) {
            throw new PlayDeletedException('Deleted play cannot have players added.');
        }

        $this->players->add($player);
    }

    public function replacePlayers(Players $newPlayers): void
    {
        if ($this->lifecycle === PlayLifecycle::Deleted) {
            throw new PlayDeletedException('Deleted play cannot have players replaced.');
        }

        $this->players->clear();

        foreach ($newPlayers as $player) {
            $this->players->add($player);
        }
    }

    /** @return Players */
    public function getPlayers()
    {
        return $this->players;
    }

    public function finalize(?DateTime $finishedAt = null): void
    {
        if ($this->lifecycle !== PlayLifecycle::Current) {
            throw new PlayNotCurrentException('Only current plays can be finalized.');
        }

        if ($finishedAt !== null && $finishedAt->getValue() <= $this->startedAt->getValue()) {
            throw new FinishedAtBeforeStartedAtException();
        }

        $this->lifecycle = PlayLifecycle::Finished;
        $this->finishedAt = $finishedAt;
    }
}
