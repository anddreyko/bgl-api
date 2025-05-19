<?php

declare(strict_types=1);

namespace Tests\Support\Extensions;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use DG\BypassFinals;

final class DgBypassFinalsExtension extends Extension
{
    public static $events = [
        Events::MODULE_INIT => 'initExtension',
    ];

    public function initExtension(SuiteEvent $e): void
    {
        BypassFinals::enable();
        $cacheDir = codecept_root_dir() . 'var/.dg-bypass-finals.cache';
        if (
            getenv('DG_BYPASS_FINALS_ENABLED', true)
            && (is_dir($cacheDir) || (mkdir($cacheDir, 0777, true) && is_dir($cacheDir)))
        ) {
            BypassFinals::setCacheDirectory($cacheDir);
        }

        codecept_debug('DG\BypassFinals was enabled');
    }
}
