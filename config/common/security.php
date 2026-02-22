<?php

declare(strict_types=1);

use Bgl\Core\Security\PasswordHasher;
use Bgl\Infrastructure\Security\BcryptPasswordHasher;

return [
    BcryptPasswordHasher::class => static fn(): BcryptPasswordHasher => new BcryptPasswordHasher(['cost' => 12]),
    PasswordHasher::class => BcryptPasswordHasher::class,
];
