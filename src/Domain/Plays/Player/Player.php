<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Player;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\ColorTooLongException;
use Bgl\Domain\Plays\NegativeNumberException;
use Bgl\Domain\Plays\NegativeScoreException;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\TeamTagTooLongException;

final readonly class Player
{
    private function __construct(
        private Uuid $id,
        private Play $play,
        private Uuid $mateId,
        private ?int $score,
        private bool $isWinner,
        private ?string $color,
        private ?string $teamTag,
        private ?int $number,
    ) {
    }

    public static function create(
        Uuid $id,
        Play $play,
        Uuid $mateId,
        ?int $score,
        bool $isWinner,
        ?string $color,
        ?string $teamTag = null,
        ?int $number = null,
    ): self {
        if ($score !== null && $score < 0) {
            throw new NegativeScoreException();
        }

        if ($color !== null && mb_strlen($color) > 50) {
            throw new ColorTooLongException();
        }

        if ($teamTag !== null && mb_strlen($teamTag) > 50) {
            throw new TeamTagTooLongException();
        }

        if ($number !== null && $number < 0) {
            throw new NegativeNumberException();
        }

        return new self($id, $play, $mateId, $score, $isWinner, $color, $teamTag, $number);
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

    public function getTeamTag(): ?string
    {
        return $this->teamTag;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }
}
