<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Serialization;

use Bgl\Core\ValueObjects\DateTime;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\PropertyCaster;

final class CastToDateTime implements PropertyCaster
{
    #[\Override]
    public function cast(mixed $value, ObjectMapper $hydrator): mixed
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        /** @var \DateTimeInterface|string|int $value */
        return new DateTime($value);
    }
}
