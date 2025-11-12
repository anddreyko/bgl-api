<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\MessageBus\Tactician;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<mixed, \Bgl\Core\Messages\Message<mixed>>
 */
final readonly class NextHandler implements MessageHandler
{
    public function __construct(private \Closure $next)
    {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): mixed
    {
        return call_user_func($this->next, $envelope);
    }
}
