<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;

final readonly class Player
{
    private function __construct(
        private Uuid $id,
        private Play $play,
        private Uuid $mateId,
        private ?int $score,
        private bool $isWinner,
        private ?string $color,
    ) {
    }

    public static function create(
        Uuid $id,
        Play $play,
        Uuid $mateId,
        ?int $score,
        bool $isWinner,
        ?string $color,
    ): self {
        if ($score !== null && $score < 0) {
            throw new \DomainException('Score cannot be negative');
        }

        if ($color !== null && mb_strlen($color) > 50) {
            throw new \DomainException('Color is too long');
        }

        return new self($id, $play, $mateId, $score, $isWinner, $color);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPlayId(): Uuid
    {
        return $this->play->getId();
    }

    public function getMateId(): Uuid
    {
        return $this->mateId;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function isWinner(): bool
    {
        return $this->isWinner;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}
