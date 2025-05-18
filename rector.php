<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/',
    ])
    ->withSkip([
        __DIR__ . '/tests',
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        __DIR__ . '/vendor-bin',
    ])
    ->withCache(__DIR__ . '/var/.rector.cache', FileCacheStorage::class)
    // uncomment to reach your current PHP version
    ->withTypeCoverageLevel(1)
    ->withDeadCodeLevel(1)
    ->withCodeQualityLevel(1)
    ->withCodingStyleLevel(1)
    ->withPhp74Sets()
    ->withRules([
        //  TypedPropertyFromStrictConstructorRector::class
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withComposerBased(twig: true, doctrine: true, symfony: true)
    ->withSets([
    ]);
