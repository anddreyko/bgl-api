<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

return [
    LoggerInterface::class => static function (): LoggerInterface {
        if ((bool)getenv('APP_DEBUG')) {
            $logger = new Logger('application');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../../var/.logs/application.log'));
        } else {
            $logger = new NullLogger();
        }

        return $logger;
    },
];
