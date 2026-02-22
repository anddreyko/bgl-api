<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final class Command implements Message
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
