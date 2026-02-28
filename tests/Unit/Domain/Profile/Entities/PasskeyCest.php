<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\Passkey;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Profile\Entities\Passkey
 */
#[Group('auth', 'domain', 'entity')]
final class PasskeyCest
{
    public function testCreateSetsFieldsCorrectly(UnitTester $i): void
    {
        $id = new Uuid('passkey-id-1');
        $userId = new Uuid('user-id-1');
        $now = new DateTime('2026-01-15 10:00:00');

        $passkey = Passkey::create(
            id: $id,
            userId: $userId,
            credentialId: 'cred-abc123',
            credentialData: '{"key":"data"}',
            createdAt: $now,
            label: 'My YubiKey',
        );

        $i->assertSame($id, $passkey->getId());
        $i->assertSame($userId, $passkey->getUserId());
        $i->assertSame('cred-abc123', $passkey->getCredentialId());
        $i->assertSame('{"key":"data"}', $passkey->getCredentialData());
        $i->assertSame(0, $passkey->getCounter());
        $i->assertSame($now, $passkey->getCreatedAt());
        $i->assertSame('My YubiKey', $passkey->getLabel());
    }

    public function testCreateWithoutLabelDefaultsToNull(UnitTester $i): void
    {
        $passkey = Passkey::create(
            id: new Uuid('passkey-id-2'),
            userId: new Uuid('user-id-1'),
            credentialId: 'cred-def456',
            credentialData: '{}',
            createdAt: new DateTime('2026-01-15 10:00:00'),
        );

        $i->assertNull($passkey->getLabel());
    }

    public function testCreateSetsCounterToZero(UnitTester $i): void
    {
        $passkey = Passkey::create(
            id: new Uuid('passkey-id-3'),
            userId: new Uuid('user-id-1'),
            credentialId: 'cred-ghi789',
            credentialData: '{}',
            createdAt: new DateTime('2026-01-15 10:00:00'),
        );

        $i->assertSame(0, $passkey->getCounter());
    }

    public function testUpdateCounterChangesValue(UnitTester $i): void
    {
        $passkey = Passkey::create(
            id: new Uuid('passkey-id-4'),
            userId: new Uuid('user-id-1'),
            credentialId: 'cred-jkl012',
            credentialData: '{}',
            createdAt: new DateTime('2026-01-15 10:00:00'),
        );

        $i->assertSame(0, $passkey->getCounter());

        $passkey->updateCounter(5);

        $i->assertSame(5, $passkey->getCounter());

        $passkey->updateCounter(10);

        $i->assertSame(10, $passkey->getCounter());
    }
}
