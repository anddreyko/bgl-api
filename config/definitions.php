<?php

declare(strict_types=1);

return array_merge_recursive(
    ...array_map(
        static function (string $file): array {
            /**
             * @var mixed[] $config
             * @psalm-suppress UnresolvableInclude
             */
            $config = require_once $file;

            return $config;
        },
        glob(__DIR__ . '/base/*.php') ?: []
    )
);
