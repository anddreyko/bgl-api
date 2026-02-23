<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Auth\AuthenticationException
 * @covers \Bgl\Core\Auth\InvalidCredentialsException
 * @covers \Bgl\Core\Auth\EmailNotConfirmedException
 * @covers \Bgl\Core\Auth\InvalidRefreshTokenException
 * @covers \Bgl\Core\Auth\UserNotActiveException
 */
#[Group('auth', 'exception')]
final class AuthenticationExceptionCest
{
    public function testAuthenticationExceptionExtendsRuntimeException(UnitTester $i): void
    {
        $exception = new AuthenticationException();

        $i->assertInstanceOf(\RuntimeException::class, $exception);
        $i->assertSame('Authentication failed', $exception->getMessage());
    }

    public function testAuthenticationExceptionWithCustomMessage(UnitTester $i): void
    {
        $exception = new AuthenticationException('Custom auth error');

        $i->assertSame('Custom auth error', $exception->getMessage());
    }

    public function testInvalidCredentialsExceptionHierarchy(UnitTester $i): void
    {
        $exception = new InvalidCredentialsException();

        $i->assertInstanceOf(AuthenticationException::class, $exception);
        $i->assertInstanceOf(\RuntimeException::class, $exception);
        $i->assertSame('Invalid credentials', $exception->getMessage());
    }

    public function testEmailNotConfirmedExceptionHierarchy(UnitTester $i): void
    {
        $exception = new EmailNotConfirmedException();

        $i->assertInstanceOf(AuthenticationException::class, $exception);
        $i->assertInstanceOf(\RuntimeException::class, $exception);
        $i->assertSame('Email not confirmed', $exception->getMessage());
    }

    public function testInvalidRefreshTokenExceptionHierarchy(UnitTester $i): void
    {
        $exception = new InvalidRefreshTokenException();

        $i->assertInstanceOf(AuthenticationException::class, $exception);
        $i->assertInstanceOf(\RuntimeException::class, $exception);
        $i->assertSame('Invalid refresh token', $exception->getMessage());
    }

    public function testUserNotActiveExceptionHierarchy(UnitTester $i): void
    {
        $exception = new UserNotActiveException();

        $i->assertInstanceOf(AuthenticationException::class, $exception);
        $i->assertInstanceOf(\RuntimeException::class, $exception);
        $i->assertSame('User is not active', $exception->getMessage());
    }
}
