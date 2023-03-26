<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\ORM\Tools\Console\Command\AbstractEntityManagerCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @var Container $container
 * @psalm-suppress UnnecessaryVarAnnotation
 */
$container = require_once __DIR__ . '/../config/container.php';

$app = new Application('Console');

/** @var class-string<Symfony\Component\Console\Command\Command> $name */
foreach ($container->get('console')['commands'] ?? [] as $name) {
    /** @var Symfony\Component\Console\Command\Command $command */
    $command = $container->get($name);
    if ($command instanceof AbstractEntityManagerCommand) {
        /** @var Symfony\Component\Console\Command\Command $command */
        $command = $container->make($name, ['entityManagerProvider' => $container->get(EntityManagerProvider::class)]);
    }
    $app->add($command);
}

$app->run();
