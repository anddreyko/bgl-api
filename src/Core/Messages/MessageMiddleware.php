<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

interface MessageMiddleware
{
    /**
     * @template TResult
     * @template TMessage of Message<TResult>
     * @template TEnvelope of Envelope<TMessage>
     * @template THandler of MessageHandler<TResult, TMessage>
     *
     * @param TEnvelope $envelope
     * @param THandler $handler
     *
     * @return TResult
     */
    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed;
}
