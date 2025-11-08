<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Extensions;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;

final class CliRunnerExtension extends Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
    ];

    public function beforeSuite(SuiteEvent $e): void
    {
        if (!is_array($this->config['commands'])) {
            \codecept_debug('Commands is required and must been array of strings.');
            exit(0);
        }

        /** @var \Codeception\Module\Cli $cli */
        $cli = $this->getModule('Cli');
        foreach ($this->config['commands'] as $command) {
            if (is_string($command)) {
                $cli->runShellCommand($command);
            } else {
                exit(0);
            }
        }
    }
}
