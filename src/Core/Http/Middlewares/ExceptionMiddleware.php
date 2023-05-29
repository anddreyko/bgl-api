<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use App\Core\Http\Enums\HttpCodesEnum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\ExceptionMiddlewareTest
 */
final class ExceptionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $exception) {
            $exception = new HttpException(
                $request,
                $exception->getMessage(),
                (int)($exception->getCode() ?: HttpCodesEnum::InternalServerError->value),
                $exception
            );
            $exception->setTitle($exception->getMessage() ?: 'Unexpected error');

            throw $exception;
        }
    }
}
