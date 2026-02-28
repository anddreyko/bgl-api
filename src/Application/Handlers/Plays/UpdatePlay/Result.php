<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

final readonly class Result
{
    public function __construct(
        public string $sessionId,
    ) {
    }
}
