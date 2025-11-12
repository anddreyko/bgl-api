<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Psr\Log\LoggerInterface;

final readonly class PingTacticianHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(Ping $ping): void
    {
        $this->logger->info($ping->text);
    }
}
