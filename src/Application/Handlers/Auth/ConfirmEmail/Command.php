<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $credential
     */
    public function __construct(
        public string $credential,
        public string $type,
    ) {
    }
}
