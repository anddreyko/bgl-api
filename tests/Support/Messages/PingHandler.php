<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<string, Ping>
 */
final class PingHandler implements MessageHandler
{
    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        return $envelope->message->text;
    }
}
