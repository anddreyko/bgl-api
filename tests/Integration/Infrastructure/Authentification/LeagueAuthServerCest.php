<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Infrastructure\Authentification;

use Bgl\Core\Auth\GrantType;
use Bgl\Core\Auth\Identities;
use Bgl\Core\Auth\Identity;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Infrastructure\Authentification\OpenAuth\LeagueAuthServer;
use Bgl\Tests\Support\IntegrationTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Authentification\OpenAuth\LeagueAuthServer
 */
#[Group('infrastructure', 'auth', 'leagueAuthServer')]
final class LeagueAuthServerCest
{
    public function testSupportsCredentialGrant(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->assertTrue($server->supports(GrantType::Credential));
    }

    public function testSupportsPasskeyGrant(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->assertTrue($server->supports(GrantType::Passkey));
    }

    public function testAuthenticateByCredentialsSuccess(IntegrationTester $i): void
    {
        $expectedIdentity = new Identity(new Uuid('550e8400-e29b-41d4-a716-446655440000'));
        $identities = $this->createMockIdentities(credentialIdentity: $expectedIdentity);
        $server = new LeagueAuthServer($identities);

        $result = $server->authenticate(
            GrantType::Credential,
            ['username' => 'test@example.com', 'password' => 'secret']
        );

        $i->assertTrue($result->equals($expectedIdentity));
    }

    public function testAuthenticateByCredentialsFailure(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Credential,
                ['username' => 'test@example.com', 'password' => 'wrong']
            )
        );
    }

    public function testAuthenticateByCredentialsMissingUsername(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Credential,
                ['password' => 'secret']
            )
        );
    }

    public function testAuthenticateByCredentialsMissingPassword(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Credential,
                ['username' => 'test@example.com']
            )
        );
    }

    public function testAuthenticateByCredentialsEmptyUsername(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Credential,
                ['username' => '', 'password' => 'secret']
            )
        );
    }

    public function testAuthenticateByPasskeySuccess(IntegrationTester $i): void
    {
        $expectedIdentity = new Identity(new Uuid('550e8400-e29b-41d4-a716-446655440000'));
        $identities = $this->createMockIdentities(idIdentity: $expectedIdentity);
        $server = new LeagueAuthServer($identities);

        $result = $server->authenticate(
            GrantType::Passkey,
            ['userId' => '550e8400-e29b-41d4-a716-446655440000']
        );

        $i->assertTrue($result->equals($expectedIdentity));
    }

    public function testAuthenticateByPasskeyFailure(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Passkey,
                ['userId' => 'nonexistent']
            )
        );
    }

    public function testAuthenticateByPasskeyMissingUserId(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Passkey,
                []
            )
        );
    }

    public function testAuthenticateByPasskeyEmptyUserId(IntegrationTester $i): void
    {
        $identities = $this->createMockIdentities();
        $server = new LeagueAuthServer($identities);

        $i->expectThrowable(
            InvalidCredentialsException::class,
            static fn() => $server->authenticate(
                GrantType::Passkey,
                ['userId' => '']
            )
        );
    }

    private function createMockIdentities(
        ?Identity $credentialIdentity = null,
        ?Identity $idIdentity = null
    ): Identities {
        return new readonly class ($credentialIdentity, $idIdentity) implements Identities {
            public function __construct(
                private ?Identity $credentialIdentity,
                private ?Identity $idIdentity
            ) {
            }

            #[\Override]
            public function findByCredentials(string $username, string $password): ?Identity
            {
                return $this->credentialIdentity;
            }

            #[\Override]
            public function findById(string $id): ?Identity
            {
                return $this->idIdentity;
            }
        };
    }
}
