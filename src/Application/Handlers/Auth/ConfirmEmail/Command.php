<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<string>
 */
final class Command implements Message
{
    public function __construct(
        public string $token,
    ) {
    }
}
