<?php

declare(strict_types=1);

return static function (Slim\App $app) {
    $app->get('/', App\Http\Actions\HelloWorldAction::class);
};
