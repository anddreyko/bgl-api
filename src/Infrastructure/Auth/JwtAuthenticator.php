<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Auth\AuthPayload;
use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Core\Security\Hasher;
use Bgl\Core\Security\TokenPayload;
use Bgl\Core\Security\Tokenizer;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;

/**
 * @see \Bgl\Tests\Unit\Infrastructure\Auth\JwtAuthenticatorCest
 */
final readonly class JwtAuthenticator implements Authenticator
{
    public function __construct(
        private Tokenizer $tokenizer,
        private Users $users,
        private Hasher $passwordHasher,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    #[\Override]
    public function login(string $email, string $password): TokenPair
    {
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        if (!$this->passwordHasher->verify($password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException();
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new EmailNotConfirmedException();
        }

        $userId = $user->getId()->getValue();
        if ($userId === null) {
            throw new AuthenticationException('Unauthorized');
        }

        return $this->tokenIssuer->issue($userId);
    }

    #[\Override]
    public function refresh(string $refreshToken): TokenPair
    {
        $payload = $this->verifyToken($refreshToken);

        if ($payload->getString('type') !== 'refresh') {
            throw new InvalidRefreshTokenException();
        }

        $user = $this->findUserFromPayload($payload);
        $this->checkTokenVersion($payload, $user);

        $userId = $user->getId()->getValue();
        if ($userId === null) {
            throw new AuthenticationException('Unauthorized');
        }

        return $this->tokenIssuer->issue($userId);
    }

    #[\Override]
    public function revoke(string $userId): void
    {
        $user = $this->users->find($userId);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        $user->incrementTokenVersion();
        $this->users->add($user);
    }

    #[\Override]
    public function verify(string $accessToken): AuthPayload
    {
        $payload = $this->verifyToken($accessToken);

        $type = $payload->getString('type');
        if ($type !== null && $type !== 'access') {
            throw new AuthenticationException('Unauthorized');
        }

        $user = $this->findUserFromPayload($payload);
        $this->checkTokenVersion($payload, $user);

        $userId = $user->getId()->getValue();
        if ($userId === null) {
            throw new AuthenticationException('Unauthorized');
        }

        return new AuthPayload(userId: $userId);
    }

    private function verifyToken(string $token): TokenPayload
    {
        try {
            return $this->tokenizer->verify($token);
        } catch (\RuntimeException $e) {
            throw new AuthenticationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function findUserFromPayload(TokenPayload $payload): User
    {
        $userId = $payload->getString('userId');
        if ($userId === null) {
            throw new AuthenticationException('Unauthorized');
        }

        $user = $this->users->find($userId);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new UserNotActiveException();
        }

        return $user;
    }

    private function checkTokenVersion(TokenPayload $payload, User $user): void
    {
        $payloadVersion = $payload->getInt('tokenVersion') ?? 0;

        if ($payloadVersion !== $user->getTokenVersion()) {
            throw new AuthenticationException('Token has been revoked');
        }
    }
}
