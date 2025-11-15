<?php

declare(strict_types=1);

namespace Bgl\Application\Aspects;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Messages\MessageMiddleware;
use Psr\Log\LoggerInterface;

/**
 * @see \Bgl\Tests\Functional\LoggingAspectCest
 */
final readonly class Logging implements MessageMiddleware
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[\Override]
    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed
    {
        $this->logger->info(
            'Start handle {message_class}',
            ['message_class' => $envelope->message::class, 'envelope' => $envelope]
        );

        try {
            $result = $handler($envelope);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Error handle {message_class}',
                ['message_class' => $envelope->message::class, 'envelope' => $envelope, 'exception' => $exception]
            );

            throw $exception;
        }

        $this->logger->info(
            'Finish handle {message_class}',
            ['message_class' => $envelope->message::class, 'envelope' => $envelope]
        );

        return $result;
    }
}
