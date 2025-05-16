<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

use function Sentry\captureException;

final class SentryHandler implements ErrorHandlerInterface
{
    public function __construct(private ErrorHandlerInterface $nextHandler)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        switch (true) {
            case $exception instanceof NotFoundException:
            case $exception instanceof UnauthorizedException:
                break;

            default:
                captureException($exception);
                break;
        }

        return ($this->nextHandler)(
            $request,
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
        );
    }
}
