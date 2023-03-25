<?php

declare(strict_types=1);

return static function (Slim\App $app, Psr\Container\ContainerInterface $container) {
    /** @var array{debug?: bool} $config */
    $config = $container->get('config');
    $app->addErrorMiddleware($config['debug'] ?? false, true, true);
};
