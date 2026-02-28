<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/',
    ])
    ->withSkip([
        __DIR__ . '/tests/Support/_generated',
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        __DIR__ . '/vendor-bin',
        NewlineBetweenClassLikeStmtsRector::class,
    ])
    ->withCache(__DIR__ . '/var/.rector.cache', FileCacheStorage::class)
    ->withPhpSets(php85: true)
    ->withTypeCoverageLevel(1)
    ->withDeadCodeLevel(1)
    ->withCodeQualityLevel(1)
    ->withCodingStyleLevel(1)
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withConfiguredRule(
        AddOverrideAttributeToOverriddenMethodsRector::class,
        [AddOverrideAttributeToOverriddenMethodsRector::ALLOW_OVERRIDE_EMPTY_METHOD => true]
    )
    ->withComposerBased(twig: true, doctrine: true, symfony: true)
    ->withSets([
    ]);
