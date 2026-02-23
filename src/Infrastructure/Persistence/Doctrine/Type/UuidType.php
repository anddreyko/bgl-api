<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Type;

use Bgl\Core\ValueObjects\Uuid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

final class UuidType extends GuidType
{
    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Uuid
    {
        if ($value === null) {
            return null;
        }

        $stringValue = (string)$value;

        return $stringValue !== '' ? new Uuid($stringValue) : null;
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value->getValue();
        }

        return (string)$value;
    }
}
