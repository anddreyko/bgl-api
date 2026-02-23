<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Presentation\Api\Interceptors\AuthInterceptor
 */
#[Group('auth', 'interceptor')]
final class AuthInterceptorCest
{
    private function makeUser(int $tokenVersion = 1): User
    {
        $user = new User(
            id: new Uuid('user-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
            status: UserStatus::Active,
        );

        // Set tokenVersion to desired value by incrementing from default (1)
        for ($v = 1; $v < $tokenVersion; $v++) {
            $user->incrementTokenVersion();
        }

        return $user;
    }

    public function testValidAccessTokenSetsUserId(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-123', 'type' => 'access', 'tokenVersion' => 1],
        ]);
        $users = Stub::makeEmpty(Users::class, [
            'find' => fn(): User => $this->makeUser(1),
        ]);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer valid-token']);

        $result = $interceptor->process($request);

        $i->assertSame('user-123', $result->getAttribute('auth.userId'));
    }

    public function testMissingAuthorizationHeaderThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test');

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testNonBearerHeaderThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Basic abc123']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testInvalidTokenThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static function (): never {
                throw new \RuntimeException('Invalid token');
            },
        ]);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer bad-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testTokenVerifyRuntimeExceptionWrapped(UnitTester $i): void
    {
        $original = new \RuntimeException('Token expired', 42);
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static function () use ($original): never {
                throw $original;
            },
        ]);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer expired-token']);

        try {
            $interceptor->process($request);
            $i->fail('Expected AuthenticationException was not thrown');
        } catch (AuthenticationException $e) {
            $i->assertSame('Token expired', $e->getMessage());
            $i->assertSame(42, $e->getCode());
            $i->assertSame($original, $e->getPrevious());
        }
    }

    public function testTokenWithoutUserIdThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['type' => 'access'],
        ]);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer token-no-user']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testRefreshTokenTypeThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-123', 'type' => 'refresh'],
        ]);
        $users = Stub::makeEmpty(Users::class);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer refresh-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testTokenWithoutTypeIsAccepted(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-456', 'tokenVersion' => 1],
        ]);
        $users = Stub::makeEmpty(Users::class, [
            'find' => fn(): User => new User(
                id: new Uuid('user-456'),
                email: new Email('test@example.com'),
                passwordHash: 'hashed',
                createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
                status: UserStatus::Active,
            ),
        ]);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer token-no-type']);

        $result = $interceptor->process($request);

        $i->assertSame('user-456', $result->getAttribute('auth.userId'));
    }

    public function testTokenVersionMismatchThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-123', 'type' => 'access', 'tokenVersion' => 1],
        ]);
        $users = Stub::makeEmpty(Users::class, [
            'find' => fn(): User => $this->makeUser(2), // user has tokenVersion=2, token has 1
        ]);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer old-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testUserNotFoundInInterceptorThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'nonexistent', 'type' => 'access', 'tokenVersion' => 1],
        ]);
        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): ?User => null,
        ]);
        $interceptor = new AuthInterceptor($tokenGen, $users);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer valid-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }
}
