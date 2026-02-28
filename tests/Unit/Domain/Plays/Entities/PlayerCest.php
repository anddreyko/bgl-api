<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Entities\Player
 */
#[Group('plays', 'player')]
final class PlayerCest
{
    private Play $play;

    public function _before(): void
    {
        $this->play = Play::create(
            new Uuid('play-1'),
            new Uuid('user-1'),
            null,
            new \DateTimeImmutable(),
        );
    }

    public function testCreateReturnsPlayerWithAllFields(UnitTester $i): void
    {
        $id = new Uuid('player-1');
        $mateId = new Uuid('mate-1');

        $player = Player::create($id, $this->play, $mateId, 42, true, 'red');

        $i->assertSame($id, $player->getId());
        $i->assertSame($this->play->getId(), $player->getPlayId());
        $i->assertSame($mateId, $player->getMateId());
        $i->assertSame(42, $player->getScore());
        $i->assertTrue($player->isWinner());
        $i->assertSame('red', $player->getColor());
    }

    public function testCreateWithNullableFields(UnitTester $i): void
    {
        $player = Player::create(
            new Uuid('player-2'),
            $this->play,
            new Uuid('mate-2'),
            null,
            false,
            null,
        );

        $i->assertNull($player->getScore());
        $i->assertFalse($player->isWinner());
        $i->assertNull($player->getColor());
    }

    public function testCreateThrowsOnNegativeScore(UnitTester $i): void
    {
        $i->expectThrowable(
            new \DomainException('Score cannot be negative'),
            fn() => Player::create(
                new Uuid('player-3'),
                $this->play,
                new Uuid('mate-3'),
                -1,
                false,
                null,
            ),
        );
    }

    public function testCreateAllowsZeroScore(UnitTester $i): void
    {
        $player = Player::create(
            new Uuid('player-4'),
            $this->play,
            new Uuid('mate-4'),
            0,
            false,
            null,
        );

        $i->assertSame(0, $player->getScore());
    }

    public function testCreateThrowsOnColorTooLong(UnitTester $i): void
    {
        $longColor = str_repeat('a', 51);

        $i->expectThrowable(
            new \DomainException('Color is too long'),
            fn() => Player::create(
                new Uuid('player-5'),
                $this->play,
                new Uuid('mate-5'),
                null,
                false,
                $longColor,
            ),
        );
    }

    public function testCreateAllowsColorExactly50Chars(UnitTester $i): void
    {
        $color = str_repeat('a', 50);

        $player = Player::create(
            new Uuid('player-6'),
            $this->play,
            new Uuid('mate-6'),
            null,
            false,
            $color,
        );

        $i->assertSame($color, $player->getColor());
    }
}
