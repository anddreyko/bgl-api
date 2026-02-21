<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

use Bgl\Core\ValueObjects\Uuid;

final readonly class Identity
{
    public function __construct(
        private Uuid $id,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        return $this->id->getValue() === $other->id->getValue();
    }
}
