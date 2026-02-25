<?php

declare(strict_types=1);

use Bgl\Infrastructure\Security\BcryptHasher;

return [
    BcryptHasher::class => static fn(): BcryptHasher => new BcryptHasher(['cost' => 4]),
];
