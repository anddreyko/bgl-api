<?php

declare(strict_types=1);

namespace App\Core\Logger\Handlers;

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
        captureException($exception);

        return ($this->nextHandler)(
            $request,
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails,
        );
    }
}
