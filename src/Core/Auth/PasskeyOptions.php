<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final readonly class PasskeyOptions
{
    public function __construct(
        public string $optionsJson,
        public string $challenge,
    ) {
    }
}
