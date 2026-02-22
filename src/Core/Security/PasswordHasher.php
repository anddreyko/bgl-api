<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $hashedPassword): bool;

    public function needsRehash(string $hashedPassword): bool;
}
