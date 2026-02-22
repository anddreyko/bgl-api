<?php

declare(strict_types=1);

use Bgl\Infrastructure\Security\BcryptPasswordHasher;

return [
    BcryptPasswordHasher::class => static fn(): BcryptPasswordHasher => new BcryptPasswordHasher(['cost' => 4]),
];
