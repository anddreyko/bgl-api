<?php

declare(strict_types=1);

namespace App\Auth\Types;

use App\Auth\ValueObjects\WebToken;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

final class WebTokenType extends TextType
{
    public const NAME = 'web_token';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof WebToken ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WebToken
    {
        return !is_string($value) || empty(trim($value)) ? null : new WebToken($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
