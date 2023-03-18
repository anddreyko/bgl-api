<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;

class HttpHelper
{
    public static function json(ResponseInterface $response, mixed $content): ResponseInterface
    {
        $response->getBody()->write(json_encode($content, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
