<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<null, \Bgl\Tests\Support\Messages\Thrown>
 */
final class ThrownHandler implements MessageHandler
{
    #[\Override]
    public function __invoke(Envelope $envelope): null
    {
        throw $envelope->message->thrown;
    }
}
