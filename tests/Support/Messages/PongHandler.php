<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Psr\Log\LoggerInterface;

/**
 * @implements MessageHandler<null, Pong>
 */
final readonly class PongHandler implements MessageHandler
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): null
    {
        $this->logger->info($envelope->message->text);

        return null;
    }
}
