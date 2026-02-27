<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\WebTester;
final class GamesCest
{
    public function _before(WebTester $i): void
    {
        $i->haveHttpHeader('Content-Type', 'application/json');
    }

    public function testSearchRequiresQuery(WebTester $i): void
    {
        $i->sendGet('/v1/games/search');
        $i->seeResponseCodeIs(422);
    }

    public function testSearchQueryTooShort(WebTester $i): void
    {
        $i->sendGet('/v1/games/search?q=ab');
        $i->seeResponseCodeIs(422);
    }
}
