<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

final readonly class EnvelopeFactory
{
    /**
     * @template TResult
     * @template TMessage of Message<TResult>
     *
     * @param TMessage $message
     * @param non-empty-string $messageId
     * @param Envelope|null $parent
     *
     * @return Envelope<TMessage>
     */
    public function build(Message $message, string $messageId, ?Envelope $parent = null): Envelope
    {
        /** @var Envelope<TMessage> */
        return new Envelope(
            $message,
            $messageId,
            $parent?->messageId,
            $parent->traceId ?? $messageId
        );
    }
}
