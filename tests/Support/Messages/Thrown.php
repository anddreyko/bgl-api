<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<null>
 */
final class Thrown implements Message
{
    public function __construct(
        public \Throwable $thrown
    ) {
    }
}
