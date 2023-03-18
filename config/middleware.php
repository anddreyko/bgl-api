<?php

declare(strict_types=1);

return static function (Slim\App $app, Di\Container $container) {
    $app->addErrorMiddleware($container->get('config')['debug'] ?? false, true, true);
};
