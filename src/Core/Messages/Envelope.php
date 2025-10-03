<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

/**
 * @template-covariant TMessage of Message<mixed> = Message<mixed>
 */
final readonly class Envelope
{
    /**
     * @param TMessage $message
     * @param non-empty-string $messageId
     * @param non-empty-string|null $parentId
     * @param non-empty-string|null $traceId
     * @param \DateTimeInterface|null $at
     * @param mixed[] $headers
     */
    public function __construct(
        public Message $message,
        public string $messageId,
        public ?string $parentId = null,
        public ?string $traceId = null,
        public ?\DateTimeInterface $at = null,
        public array $headers = []
    ) {
    }
}
