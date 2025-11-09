<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Messages\MessageMiddleware;
use Psr\Log\LoggerInterface;

final readonly class Logging implements MessageMiddleware
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override]
    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed
    {
        $this->logger->info("message id: {msg}", ['msg' => $envelope->messageId]);

        return $handler($envelope);
    }
}
