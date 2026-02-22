<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\OpenSession;

final readonly class Result
{
    public function __construct(
        public string $sessionId,
    ) {
    }
}
