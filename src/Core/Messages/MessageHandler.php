<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMessage of Message<TResult>
 */
interface MessageHandler
{
    /**
     * @template TEnvelope of Envelope<TMessage>
     * @param TEnvelope $envelope
     *
     * @return TResult
     */
    public function __invoke(Envelope $envelope): mixed;
}
