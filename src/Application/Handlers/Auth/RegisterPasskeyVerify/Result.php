<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RegisterPasskeyVerify;

final readonly class Result
{
    public function __construct(
        public string $message,
    ) {
    }
}
