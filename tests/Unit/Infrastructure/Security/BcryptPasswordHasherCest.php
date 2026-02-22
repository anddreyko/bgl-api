<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Security;

use Bgl\Infrastructure\Security\BcryptPasswordHasher;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Security\BcryptPasswordHasher
 */
#[Group('core', 'security', 'passwordHasher')]
final class BcryptPasswordHasherCest
{
    private BcryptPasswordHasher $hasher;

    public function _before(): void
    {
        $this->hasher = new BcryptPasswordHasher(['cost' => 4]);
    }

    public function testHashReturnsNonEmptyString(UnitTester $i): void
    {
        $hash = $this->hasher->hash('password123');

        $i->assertNotEmpty($hash);
    }

    public function testHashReturnsDifferentStringFromPlainPassword(UnitTester $i): void
    {
        $plain = 'password123';
        $hash = $this->hasher->hash($plain);

        $i->assertNotEquals($plain, $hash);
    }

    public function testVerifyCorrectPassword(UnitTester $i): void
    {
        $plain = 'password123';
        $hash = $this->hasher->hash($plain);

        $i->assertTrue($this->hasher->verify($plain, $hash));
    }

    public function testVerifyWrongPassword(UnitTester $i): void
    {
        $hash = $this->hasher->hash('password123');

        $i->assertFalse($this->hasher->verify('wrongPassword', $hash));
    }

    public function testNeedsRehashReturnsFalseForFreshHash(UnitTester $i): void
    {
        $hash = $this->hasher->hash('password123');

        $i->assertFalse($this->hasher->needsRehash($hash));
    }

    public function testNeedsRehashReturnsTrueForDifferentCost(UnitTester $i): void
    {
        $lowCostHasher = new BcryptPasswordHasher(['cost' => 4]);
        $hash = $lowCostHasher->hash('password123');

        $highCostHasher = new BcryptPasswordHasher(['cost' => 10]);

        $i->assertTrue($highCostHasher->needsRehash($hash));
    }

    public function testHashProducesDifferentHashesForSamePassword(UnitTester $i): void
    {
        $plain = 'password123';
        $hash1 = $this->hasher->hash($plain);
        $hash2 = $this->hasher->hash($plain);

        $i->assertNotEquals($hash1, $hash2);
    }
}
