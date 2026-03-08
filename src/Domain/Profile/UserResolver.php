<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

use Bgl\Core\ValueObjects\Uuid;

final readonly class UserResolver
{
    public function __construct(
        private Users $users,
    ) {
    }

    public function resolve(string $identifier): ?User
    {
        if (Uuid::isValid($identifier)) {
            return $this->users->find($identifier);
        }

        return $this->users->findByName($identifier);
    }

    public function resolveId(string $identifier): ?string
    {
        if (Uuid::isValid($identifier)) {
            return $identifier;
        }

        return $this->users->findByName($identifier)?->getId()->getValue();
    }
}
