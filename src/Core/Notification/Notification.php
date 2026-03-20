<?php

declare(strict_types=1);

namespace Bgl\Core\Notification;

final readonly class Notification
{
    /**
     * @param non-empty-string $to
     * @param non-empty-string $subject
     * @param non-empty-string $body
     * @param non-empty-string|null $from
     */
    public function __construct(
        public string $to,
        public string $subject,
        public string $body,
        public ?string $from = null,
    ) {
    }
}
