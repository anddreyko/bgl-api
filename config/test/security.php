<?php

declare(strict_types=1);

use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Infrastructure\Auth\FakePasskeyVerifier;
use Bgl\Infrastructure\Security\BcryptHasher;

return [
    BcryptHasher::class => static fn(): BcryptHasher => new BcryptHasher(['cost' => 4]),
    PasskeyVerifier::class => static fn(): PasskeyVerifier => new FakePasskeyVerifier(),
];
