<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Core\ValueObjects\PasswordHash;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\Helpers\PasswordHashHelperTest
 */
final readonly class PasswordHasher
{
    public function __construct(private int $memoryCost = PASSWORD_ARGON2_DEFAULT_MEMORY_COST)
    {
    }

    public function hash(string $value): PasswordHash
    {
        $value = \trim($value);
        Assert::notEmpty($value);

        return new PasswordHash(password_hash($value, PASSWORD_ARGON2I, ['memory_cost' => $this->memoryCost]));
    }

    public function validate(string $value, PasswordHash $hash): bool
    {
        $value = \trim($value);
        Assert::notEmpty($value);

        return password_verify($value, $hash->getValue());
    }
}
