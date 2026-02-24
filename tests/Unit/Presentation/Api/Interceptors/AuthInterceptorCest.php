<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\AuthPayload;
use Bgl\Core\Auth\Authenticator;
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
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'verify' => static fn(): AuthPayload => new AuthPayload(userId: 'user-123'),
        ]);
        $interceptor = new AuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer valid-token']);

        $result = $interceptor->process($request);

        $i->assertSame('user-123', $result->getAttribute('auth.userId'));
    }

    public function testMissingAuthorizationHeaderThrows(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class);
        $interceptor = new AuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test');

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testNonBearerHeaderThrows(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class);
        $interceptor = new AuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Basic abc123']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }

    public function testAuthenticatorExceptionPropagates(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'verify' => static function (): never {
                throw new AuthenticationException('Token expired');
            },
        ]);
        $interceptor = new AuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer bad-token']);

        $i->expectThrowable(AuthenticationException::class, static function () use ($interceptor, $request): void {
            $interceptor->process($request);
        });
    }
}
