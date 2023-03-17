<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get(
    '/',
    function (RequestInterface $request, ResponseInterface $response) {
        $response->getBody()->write('{"content": "Hello world!"}');

        return $response->withHeader('Content-Type', 'application/json');
    }
);

$app->run();
