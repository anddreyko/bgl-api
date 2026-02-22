<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Security;

use Bgl\Core\Security\PasswordHasher;

/**
 * @psalm-type BcryptOptions = array{cost?: int}
 */
final readonly class BcryptPasswordHasher implements PasswordHasher
{
    /**
     * @param BcryptOptions $options
     */
    public function __construct(
        private array $options = [],
    ) {
    }

    #[\Override]
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, $this->options);
    }

    #[\Override]
    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    #[\Override]
    public function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, PASSWORD_BCRYPT, $this->options);
    }
}
