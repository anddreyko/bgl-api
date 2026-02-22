<?php

declare(strict_types=1);

namespace Bgl\Core\Validation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final readonly class ValidUuid
{
    public function __construct(
        public string $message = 'This value is not a valid UUID.',
    ) {
    }
}
