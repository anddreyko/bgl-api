<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (): LoggerInterface {
        $logger = new Logger('application');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../../var/.logs/application.log'));

        return $logger;
    },
];
