<?php

declare(strict_types=1);

namespace App\Core\Http\Helpers;

use Psr\Http\Message\ResponseInterface;

/**
 * @see \Tests\Unit\Core\Http\Helpers\HttpHelperTest
 */
class HttpHelper
{
    public static function json(ResponseInterface $response, mixed $content): ResponseInterface
    {
        $response->getBody()->write(json_encode($content, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
