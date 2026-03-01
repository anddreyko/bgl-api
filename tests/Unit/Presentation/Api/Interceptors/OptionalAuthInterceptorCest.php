<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Api\Interceptors;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\AuthPayload;
use Bgl\Core\Auth\Authenticator;
use Bgl\Presentation\Api\Interceptors\OptionalAuthInterceptor;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Presentation\Api\Interceptors\OptionalAuthInterceptor
 */
#[Group('auth', 'interceptor')]
final class OptionalAuthInterceptorCest
{
    public function testWithValidTokenSetsUserId(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'verify' => static fn(): AuthPayload => new AuthPayload(userId: 'user-123'),
        ]);
        $interceptor = new OptionalAuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer valid-token']);

        $result = $interceptor->process($request);

        $i->assertSame('user-123', $result->getAttribute('auth.userId'));
    }

    public function testWithoutTokenSetsNull(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class);
        $interceptor = new OptionalAuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test');

        $result = $interceptor->process($request);

        $i->assertNull($result->getAttribute('auth.userId'));
    }

    public function testWithInvalidTokenSetsNull(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'verify' => static function (): never {
                throw new AuthenticationException('Token expired');
            },
        ]);
        $interceptor = new OptionalAuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Bearer bad-token']);

        $result = $interceptor->process($request);

        $i->assertNull($result->getAttribute('auth.userId'));
    }

    public function testWithNonBearerHeaderSetsNull(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class);
        $interceptor = new OptionalAuthInterceptor($authenticator);

        $request = new ServerRequest('GET', '/test', ['Authorization' => 'Basic abc123']);

        $result = $interceptor->process($request);

        $i->assertNull($result->getAttribute('auth.userId'));
    }
}
