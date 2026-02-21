<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Ping;

use Bgl\Core\AppVersion;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateInterval;
use Bgl\Core\ValueObjects\DateTime;
use Psr\Clock\ClockInterface;

/**
 * @see \Bgl\Tests\Functional\PingHandlerCest
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private AppVersion $version,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        $now = $this->clock->now();

        return new Result(
            datetime: new DateTime($now),
            delay: new DateInterval($envelope->message->datetime->diff($now)),
            version: $this->version->getVersion(),
            environment: (string)getenv('APP_ENV'),
            messageId: $envelope->messageId,
            parentId: $envelope->parentId,
            traceId: $envelope->traceId
        );
    }
}
