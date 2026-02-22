<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\CloseSession;

final readonly class Result
{
    public function __construct(
        public string $sessionId,
        public string $startedAt,
        public string $finishedAt,
    ) {
    }
}
