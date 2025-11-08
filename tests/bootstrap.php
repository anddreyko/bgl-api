<?php

$dirToCore = __DIR__ . '/..';

foreach (spl_autoload_functions() as $loader) {
    spl_autoload_unregister($loader);
}

$toolsAutoloader = require $dirToCore . '/vendor-bin/codeception/vendor/autoload.php';
$rootAutoloader = require $dirToCore . '/vendor/autoload.php';
$toolsAutoloader->register(true);
$rootAutoloader->register();
