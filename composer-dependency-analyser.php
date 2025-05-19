<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    //// Adjusting scanned paths
    ->addPathToScan(__DIR__ . '/cli', isDev: false)
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/helpers', isDev: false)
    ->addPathToScan(__DIR__ . '/migrations', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/templates', isDev: false)
    ->addPathToScan(__DIR__ . '/translations', isDev: false)
    ->addPathToScan(__DIR__ . '/web', isDev: false)
    ->addPathToScan(__DIR__ . '/fixtures', isDev: true)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    # ->addPathToExclude(__DIR__ . '/tests/Support/_generated')
    ->disableComposerAutoloadPathScan() // disable automatic scan of autoload & autoload-dev paths from composer.json
    ->setFileExtensions(['php']) // applies only to directory scanning, not directly listed files

    //// Ignoring errors
    ->ignoreErrors([ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('symfony/config', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('phpunit/phpunit', [ErrorType::SHADOW_DEPENDENCY])
    # ->ignoreErrorsOnPackage('roave/security-advisories', [ErrorType::UNUSED_DEPENDENCY])
    # ->ignoreErrorsOnPath(__DIR__ . '/cache/DIC.php', [ErrorType::SHADOW_DEPENDENCY])
    # ->ignoreErrorsOnPackage('symfony/polyfill-php73', [ErrorType::UNUSED_DEPENDENCY])
    # ->ignoreErrorsOnPackageAndPath('symfony/console', __DIR__ . '/src/OptionalCommand.php', [ErrorType::SHADOW_DEPENDENCY])

    //// Ignoring unknown symbols
    # ->ignoreUnknownClasses(['Memcached'])
    # ->ignoreUnknownClassesRegex('~^DDTrace~')
    # ->ignoreUnknownFunctions(['opcache_invalidate'])
    # ->ignoreUnknownFunctionsRegex('~^opcache_~')

    //// Adjust analysis
    # ->enableAnalysisOfUnusedDevDependencies() // dev packages are often used only in CI, so this is not enabled by default
    # ->disableReportingUnmatchedIgnores() // do not report ignores that never matched any error
    # ->disableExtensionsAnalysis() // do not analyse ext-* dependencies

    //// Use symbols from yaml/xml/neon files
    // - designed for DIC config files (see below)
    // - beware that those are not validated and do not even trigger unknown class error
    #->addForceUsedSymbols($classesExtractedFromNeonJsonYamlXmlEtc)
    ;
