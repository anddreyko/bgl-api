<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use League\Tactician\Middleware;
use Psr\Log\LoggerInterface;

final readonly class LoggingTactician implements Middleware
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override]
    public function execute($command, callable $next)
    {
        $this->logger->info("message id: {msg}", ['msg' => $command::class]);

        return $next($command);
    }
}
