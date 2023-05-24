<?php

declare(strict_types=1);

return static function (Slim\App $app) {
    $app->get('/', Actions\SwaggerAction::class);
    $app->get('/v1/hello-world', Actions\V1\HelloWorldAction::class);
    $app->get('/v1/auth/register-by-email', Actions\V1\Auth\SignUpAction::class);
    $app->get('/v1/auth/confirm-email', Actions\V1\Auth\ConfirmEmailAction::class);
};
