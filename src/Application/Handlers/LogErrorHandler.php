<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;

use function Sentry\captureException;

/** @psalm-suppress PropertyNotSetInConstructor */
final class LogErrorHandler extends ErrorHandler
{
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        protected LoggerInterface $logger,
    ) {
        parent::__construct($callableResolver, $responseFactory, $this->logger);
    }

    protected function writeToErrorLog(): void
    {
        captureException($this->exception);
        $this->logger->error(
            $this->exception->getMessage(),
            [
                'exception' => $this->exception,
                'url' => (string)$this->request->getUri(),
            ]
        );
    }
}
