<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\GetUser;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final class Query implements Message
{
    public function __construct(
        public readonly string $userId,
    ) {
    }
}
