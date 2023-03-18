<?php

declare(strict_types=1);

return array_merge_recursive(
    ...array_map(
        static fn($file) => require_once $file,
        glob(__DIR__ . '/base/*.php') ?: []
    )
);
