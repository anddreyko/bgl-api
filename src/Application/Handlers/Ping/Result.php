<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Ping;

final readonly class Result
{
    public function __construct(
        public \DateTimeImmutable $datetime,
        public \DateInterval $delay,
        public string $version,
        public string $environment,
        public string $messageId,
        public ?string $parentId,
        public ?string $traceId,
    ) {
    }
}
