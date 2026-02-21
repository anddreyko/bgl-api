<?php

declare(strict_types=1);

namespace Bgl\Tests\Web;

use Bgl\Tests\Support\WebTester;
use Codeception\Attribute\Group;

class AccessCest
{
    #[Group('smoke')]
    public function testPing(WebTester $i): void
    {
        $i->sendGet('/ping');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType([
            'data' => [
                'datetime' => [
                    'date' => 'string',
                    'timezone_type' => 'integer',
                    'timezone' => 'string',
                ],
                'delay' => [
                    'y' => 'integer',
                    'm' => 'integer',
                    'd' => 'integer',
                    'h' => 'integer',
                    'i' => 'integer',
                    's' => 'integer',
                    'f' => 'float|integer',
                ],
                'version' => 'string',
                'environment' => 'string',
                'messageId' => 'string',
                'parentId' => 'string|null',
                'traceId' => 'string|null',
            ],
        ]);
    }
}
