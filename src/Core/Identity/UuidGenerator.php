<?php

declare(strict_types=1);

namespace Bgl\Core\Identity;

use Bgl\Core\ValueObjects\Uuid;

interface UuidGenerator
{
    public function generate(): Uuid;
}
