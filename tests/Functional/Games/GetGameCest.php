<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Games;

use Bgl\Application\Handlers\Games\GetGame\Handler;
use Bgl\Application\Handlers\Games\GetGame\Query;
use Bgl\Application\Handlers\Games\GetGame\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Games\GetGame\Handler
 */
#[Group('application', 'handler', 'games', 'get-game')]
final class GetGameCest
{
    private ?Handler $handler = null;
    private ?Games $games = null;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Games $games */
        $this->games = $container->get(Games::class);
    }

    public function _after(): void
    {
        $this->handler = null;
        $this->games = null;
    }

    public function testGetGameReturnsResult(FunctionalTester $i): void
    {
        $gameId = new Uuid('g-get-1');
        $now = new DateTime();
        $this->games->add(Game::create($gameId, 13, 'Catan', 1995, $now));

        $result = ($this->handler)(new Envelope(
            message: new Query(gameId: (string) $gameId),
            messageId: 'msg-get-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame((string) $gameId, $result->id);
        $i->assertSame(13, $result->bggId);
        $i->assertSame('Catan', $result->name);
        $i->assertSame(1995, $result->yearPublished);
    }

    public function testGetGameNotFoundThrowsException(FunctionalTester $i): void
    {
        $i->expectThrowable(NotFoundException::class, fn() => ($this->handler)(new Envelope(
            message: new Query(gameId: 'non-existent-id'),
            messageId: 'msg-get-2',
        )));
    }
}
