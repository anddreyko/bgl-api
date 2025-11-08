<?php

namespace Bgl\Tests\Cli;

use Bgl\Tests\Support\CliTester;
use Codeception\Attribute\Group;

class AccessCest
{
    #[Group('smoke')]
    public function testSuccess(CliTester $i): void
    {
        $i->runShellCommand('php cli/app', false);
        $i->seeResultCodeIs(0);
    }
}
