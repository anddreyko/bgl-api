<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Security\TokenGenerator;
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
    public function testValidAccessTokenSetsUserId(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-123', 'type' => 'access'],
        ]);
        $interceptor = new AuthInterceptor($tokenGen);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer valid-token']);

        $result = $interceptor->process($request);

        $i->assertSame('user-123', $result->getAttribute('auth.userId'));
    }

    public function testMissingAuthorizationHeaderThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class);
        $interceptor = new AuthInterceptor($tokenGen);

        $request = new ServerRequest('GET', '/test');

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testNonBearerHeaderThrows(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class);
        $interceptor = new AuthInterceptor($tokenGen);

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
        $interceptor = new AuthInterceptor($tokenGen);

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
        $interceptor = new AuthInterceptor($tokenGen);

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
        $interceptor = new AuthInterceptor($tokenGen);

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
        $interceptor = new AuthInterceptor($tokenGen);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer refresh-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testTokenWithoutTypeIsAccepted(UnitTester $i): void
    {
        $tokenGen = Stub::makeEmpty(TokenGenerator::class, [
            'verify' => static fn(): array => ['userId' => 'user-456'],
        ]);
        $interceptor = new AuthInterceptor($tokenGen);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer token-no-type']);

        $result = $interceptor->process($request);

        $i->assertSame('user-456', $result->getAttribute('auth.userId'));
    }
}
