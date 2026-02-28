<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\Collections\ArrayCollection;
use Bgl\Core\Collections\Collection;
use Bgl\Core\ValueObjects\Uuid;

final class Play
{
    /**
     * @var Collection<Player>
     * @psalm-var Collection<Player>
     */
    private $players;

    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private ?string $name,
        private PlayStatus $status,
        private readonly \DateTimeImmutable $startedAt,
        private ?\DateTimeImmutable $finishedAt,
        private ?Uuid $gameId = null,
        private Visibility $visibility = Visibility::Private,
    ) {
        /** @var ArrayCollection<Player> */
        $this->players = new ArrayCollection();
    }

    public static function create(
        Uuid $id,
        Uuid $userId,
        ?string $name,
        \DateTimeImmutable $startedAt,
        ?Uuid $gameId = null,
        Visibility $visibility = Visibility::Private,
    ): self {
        return new self(
            $id,
            $userId,
            $name,
            PlayStatus::Draft,
            $startedAt,
            null,
            $gameId,
            $visibility,
        );
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

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
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
            throw new \DomainException('Play can only be updated in draft status');
        }

        $this->name = $name;
        $this->gameId = $gameId;
        $this->visibility = $visibility;
    }

    public function changeVisibility(Visibility $visibility): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new \DomainException('Visibility can only be changed in draft status');
        }

        $this->visibility = $visibility;
    }

    public function addPlayer(Player $player): void
    {
        $this->players->add($player);
    }

    /**
     * @return array<int, Player>
     */
    public function getPlayers(): array
    {
        return $this->players->toArray();
    }

    public function close(\DateTimeImmutable $finishedAt): void
    {
        if ($this->status !== PlayStatus::Draft) {
            throw new \DomainException('Play can only be closed from draft status');
        }

        if ($finishedAt <= $this->startedAt) {
            throw new \DomainException('finishedAt must be after startedAt');
        }

        $this->status = PlayStatus::Published;
        $this->finishedAt = $finishedAt;
    }
}
