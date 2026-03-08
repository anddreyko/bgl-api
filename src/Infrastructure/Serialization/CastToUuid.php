<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Serialization;

use Bgl\Core\ValueObjects\Uuid;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\PropertyCaster;

final class CastToUuid implements PropertyCaster
{
    #[\Override]
    public function cast(mixed $value, ObjectMapper $hydrator): mixed
    {
        if ($value instanceof Uuid) {
            return $value;
        }

        $stringValue = (string)$value;

        if (!Uuid::isValid($stringValue)) {
            return new Uuid();
        }

        /** @var non-empty-string $stringValue */
        return new Uuid($stringValue);
    }
}
