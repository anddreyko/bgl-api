<?php

declare(strict_types=1);

namespace App\Domain\Auth\Types;

use App\Domain\Auth\Enums\UserStatusEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Types\Type;

final class StatusType extends Type
{
    public const NAME = 'user_status';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = \implode(
            ', ',
            \array_map(
                static fn($value) => "'" . \mb_strtolower($value->name) . "'",
                UserStatusEnum::cases()
            )
        );

        /** @var string $name */
        $name = $column['name'];

        return match (true) {
            $platform instanceof SqlitePlatform => \sprintf('TEXT CHECK(%s IN (%s))', $name, $values),
            $platform instanceof PostgreSQLPlatform, $platform instanceof SQLServerPlatform => \sprintf(
                'VARCHAR(255) CHECK(%s IN (%s))',
                $name,
                $values
            ),
            default => \sprintf('ENUM(%s)', $values),
        };
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof UserStatusEnum ? mb_strtolower($value->name) : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserStatusEnum
    {
        if (empty($value)) {
            return null;
        }

        foreach (UserStatusEnum::cases() as $case) {
            if (mb_strtolower($case->name) === $value) {
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
