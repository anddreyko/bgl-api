<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<\DateTimeImmutable, GetTimestamp>
 */
final class GetTimestampHandler implements MessageHandler
{
    #[\Override]
    public function __invoke(Envelope $envelope): \DateTimeImmutable
    {
        return new \DateTimeImmutable($envelope->message->date);
    }
}
