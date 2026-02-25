<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RegisterPasskeyVerify;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<string>
 */
final readonly class Command implements Message
{
    public function __construct(
        public string $userId,
        public string $response,
        public ?string $label = null,
    ) {
    }
}
