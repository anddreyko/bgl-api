<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

/**
 * @see \Bgl\Tests\Integration\MessageBus\BaseDispatcher
 */
interface Dispatcher
{
    /**
     * @template TResult of mixed
     * @param Message<TResult> $message
     * @param Envelope|null $parent
     *
     * @return TResult
     */
    public function dispatch(
        Message $message,
        ?Envelope $parent = null
    ): mixed;
}
