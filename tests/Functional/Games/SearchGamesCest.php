<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Games;

use Bgl\Application\Handlers\Games\SearchGames\Handler;
use Bgl\Application\Handlers\Games\SearchGames\Query;
use Bgl\Application\Handlers\Games\SearchGames\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Games\SearchGames\Handler
 */
#[Group('application', 'handler', 'games', 'search-games')]
final class SearchGamesCest
{
    private ?Handler $handler = null;
    private ?Games $games = null;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $handler = $container->get(Handler::class);
        $this->handler = $handler;

        /** @var Games $games */
        $games = $container->get(Games::class);
        $this->games = $games;
    }

    private function handler(): Handler
    {
        \assert($this->handler instanceof Handler);

        return $this->handler;
    }

    private function games(): Games
    {
        \assert($this->games instanceof Games);

        return $this->games;
    }

    public function testSearchReturnsMatchingGames(FunctionalTester $i): void
    {
        $this->seedGames();

        $result = ($this->handler())(new Envelope(
            message: new Query(q: 'catan'),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame(2, $result->total);
        $i->assertCount(2, $result->data);
        $i->assertSame('Catan', $result->data[0]['name']);
    }

    public function testSearchEmptyResults(FunctionalTester $i): void
    {
        $result = ($this->handler())(new Envelope(
            message: new Query(q: 'xyznonexistent'),
            messageId: 'msg-2',
        ));

        $i->assertSame(0, $result->total);
        $i->assertSame([], $result->data);
    }

    public function testSearchWithoutQueryReturnsAll(FunctionalTester $i): void
    {
        $this->seedGames();

        $result = ($this->handler())(new Envelope(
            message: new Query(),
            messageId: 'msg-all',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame(3, $result->total);
        $i->assertCount(3, $result->data);
    }

    public function testSearchPagination(FunctionalTester $i): void
    {
        $this->seedGames();

        $result = ($this->handler())(new Envelope(
            message: new Query(q: 'catan', page: 1, size: 1),
            messageId: 'msg-3',
        ));

        $i->assertSame(2, $result->total);
        $i->assertCount(1, $result->data);
    }

    private function seedGames(): void
    {
        $now = new DateTime();
        $this->games()->add(Game::create(new Uuid('g1'), 13, 'Catan', 1995, $now));
        $this->games()->add(Game::create(new Uuid('g2'), 27710, 'Catan: Cities & Knights', 1998, $now));
        $this->games()->add(Game::create(new Uuid('g3'), 174430, 'Gloomhaven', 2017, $now));
    }
}
