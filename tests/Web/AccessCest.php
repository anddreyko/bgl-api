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
                    'timestamp' => 'string',
                    'datetime' => 'string',
                ],
                'delay' => [
                    'seconds' => 'integer',
                    'interval' => 'string',
                ],
                'version' => 'string',
                'environment' => 'string',
                'message_id' => 'string',
                'parent_id' => 'string|null',
                'trace_id' => 'string|null',
            ],
        ]);
    }
}
