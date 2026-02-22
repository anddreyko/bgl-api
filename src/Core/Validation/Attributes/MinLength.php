<?php

declare(strict_types=1);

namespace Bgl\Core\Validation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final readonly class MinLength
{
    public function __construct(
        public int $min,
        public string $message = 'This value is too short. It should have %d characters or more.',
    ) {
    }
}
