<?php

declare(strict_types=1);

namespace App\Domain\Plays\Types;

use App\Domain\Plays\Enums\SessionStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class SessionStatusType extends IntegerType
{
    public const NAME = 'session_status';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof SessionStatus ? $value->value : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SessionStatus
    {
        if (empty($value)) {
            return null;
        }

        foreach (SessionStatus::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
