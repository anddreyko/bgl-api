<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\PasskeySignInOptions;

final readonly class Result
{
    public function __construct(
        public string $options,
    ) {
    }
}
