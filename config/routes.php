<?php

declare(strict_types=1);

return static function (Slim\App $app) {
    $app->get('/', App\Http\Actions\SwaggerAction::class);
    $app->get('/v1/hello-world', App\Http\Actions\V1\HelloWorldAction::class);
};
