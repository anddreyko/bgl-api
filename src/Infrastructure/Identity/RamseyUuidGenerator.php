<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Identity;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\ValueObjects\Uuid;
use Ramsey\Uuid\Uuid as RamseyUuid;

final readonly class RamseyUuidGenerator implements UuidGenerator
{
    #[\Override]
    public function generate(): Uuid
    {
        return new Uuid(RamseyUuid::uuid4()->toString());
    }
}
