<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\UpdateUser;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $userId
     * @param non-empty-string $name
     */
    public function __construct(
        public string $userId,
        public string $name,
    ) {
    }
}
