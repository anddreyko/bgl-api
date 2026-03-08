<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Plays\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\NegativeNumberException;
use Bgl\Domain\Plays\NegativeScoreException;
use Bgl\Domain\Plays\ColorTooLongException;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\TeamTagTooLongException;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Plays\Player\Player
 */
#[Group('plays', 'player')]
final class PlayerCest
{
    private Play $play;

    public function _before(): void
    {
        $this->play = Play::create(
            new Uuid('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c01'),
            new Uuid('e5f6a7b8-c9d0-4e1f-aa3b-4c5d6e7f8091'),
            null,
            new DateTime('now'),
            new InMemoryPlayers(),
        );
    }

    public function testCreateReturnsPlayerWithAllFields(UnitTester $i): void
    {
        $id = new Uuid('55555555-5555-4555-8555-555555555551');
        $mateId = new Uuid('66666666-6666-4666-8666-666666666661');

        $player = Player::create($id, $this->play, $mateId, 42, true, 'red', 'TeamA', 7);

        $i->assertSame($id, $player->getId());
        $i->assertSame($this->play->getId(), $player->getPlayId());
        $i->assertSame($mateId, $player->getMateId());
        $i->assertSame(42, $player->getScore());
        $i->assertTrue($player->isWinner());
        $i->assertSame('red', $player->getColor());
        $i->assertSame('TeamA', $player->getTeamTag());
        $i->assertSame(7, $player->getNumber());
    }

    public function testCreateWithNullableFields(UnitTester $i): void
    {
        $player = Player::create(
            new Uuid('55555555-5555-4555-8555-555555555552'),
            $this->play,
            new Uuid('66666666-6666-4666-8666-666666666662'),
            null,
            false,
            null,
        );

        $i->assertNull($player->getScore());
        $i->assertFalse($player->isWinner());
        $i->assertNull($player->getColor());
        $i->assertNull($player->getTeamTag());
        $i->assertNull($player->getNumber());
    }

    public function testCreateThrowsOnNegativeScore(UnitTester $i): void
    {
        $i->expectThrowable(
            new NegativeScoreException(),
            fn() => Player::create(
                new Uuid('55555555-5555-4555-8555-555555555553'),
                $this->play,
                new Uuid('66666666-6666-4666-8666-666666666663'),
                -1,
                false,
                null,
            ),
        );
    }

    public function testCreateAllowsZeroScore(UnitTester $i): void
    {
        $player = Player::create(
            new Uuid('55555555-5555-4555-8555-555555555554'),
            $this->play,
            new Uuid('66666666-6666-4666-8666-666666666664'),
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
            new ColorTooLongException(),
            fn() => Player::create(
                new Uuid('55555555-5555-4555-8555-555555555555'),
                $this->play,
                new Uuid('66666666-6666-4666-8666-666666666665'),
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
            new Uuid('55555555-5555-4555-8555-555555555556'),
            $this->play,
            new Uuid('66666666-6666-4666-8666-666666666666'),
            null,
            false,
            $color,
        );

        $i->assertSame($color, $player->getColor());
    }

    public function testCreateThrowsOnTeamTagTooLong(UnitTester $i): void
    {
        $longTag = str_repeat('a', 51);

        $i->expectThrowable(
            new TeamTagTooLongException(),
            fn() => Player::create(
                new Uuid('55555555-5555-4555-8555-555555555557'),
                $this->play,
                new Uuid('66666666-6666-4666-8666-666666666667'),
                null,
                false,
                null,
                $longTag,
            ),
        );
    }

    public function testCreateAllowsTeamTagExactly50Chars(UnitTester $i): void
    {
        $tag = str_repeat('a', 50);

        $player = Player::create(
            new Uuid('55555555-5555-4555-8555-555555555558'),
            $this->play,
            new Uuid('66666666-6666-4666-8666-666666666668'),
            null,
            false,
            null,
            $tag,
        );

        $i->assertSame($tag, $player->getTeamTag());
    }

    public function testCreateThrowsOnNegativeNumber(UnitTester $i): void
    {
        $i->expectThrowable(
            new NegativeNumberException(),
            fn() => Player::create(
                new Uuid('55555555-5555-4555-8555-555555555559'),
                $this->play,
                new Uuid('66666666-6666-4666-8666-666666666669'),
                null,
                false,
                null,
                null,
                -1,
            ),
        );
    }

    public function testCreateAllowsZeroNumber(UnitTester $i): void
    {
        $player = Player::create(
            new Uuid('55555555-5555-4555-8555-55555555555a'),
            $this->play,
            new Uuid('66666666-6666-4666-8666-66666666666a'),
            null,
            false,
            null,
            null,
            0,
        );

        $i->assertSame(0, $player->getNumber());
    }
}
