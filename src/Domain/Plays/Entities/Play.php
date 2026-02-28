<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Play
{
    /** @var Collection<int, Player> */
    private Collection $players;

    public function __construct(
        private readonly Uuid $id,
        private readonly Uuid $userId,
        private readonly ?string $name,
        private PlayStatus $status,
        private readonly \DateTimeImmutable $startedAt,
        private ?\DateTimeImmutable $finishedAt,
        private readonly ?Uuid $gameId = null,
        private Visibility $visibility = Visibility::Private,
    ) {
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
