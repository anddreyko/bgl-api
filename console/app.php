<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Psr\Container\ContainerInterface $container */
$container = require_once __DIR__ . '/../config/container.php';

$app = new Application('Console');

$em = $container->get(EntityManagerInterface::class);
$app->getHelperSet()->set(new EntityManagerHelper($em), 'em');

/** @var string $name */
foreach ($container->get('console')['commands'] ?? [] as $name) {
    /** @var Symfony\Component\Console\Command\Command $command */
    $command = $container->get($name);
    $app->add($command);
}

$app->run();
