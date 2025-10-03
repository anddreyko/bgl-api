<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

interface MessageIdGenerator
{
    /**
     * @return non-empty-string
     */
    public function generate(): string;
}
