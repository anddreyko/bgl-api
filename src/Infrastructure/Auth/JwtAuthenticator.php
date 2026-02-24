<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\AuthPayload;
use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;

/**
 * @see \Bgl\Tests\Unit\Infrastructure\Auth\JwtAuthenticatorCest
 */
final readonly class JwtAuthenticator implements Authenticator
{
    public function __construct(
        private TokenGenerator $tokenGenerator,
        private Users $users,
        private PasswordHasher $passwordHasher,
        private TokenTtlConfig $tokenTtlConfig,
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

        return $this->issueTokenPair($user);
    }

    #[\Override]
    public function refresh(string $refreshToken): TokenPair
    {
        $payload = $this->verifyToken($refreshToken);

        if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
            throw new InvalidRefreshTokenException();
        }

        $user = $this->findUserFromPayload($payload);
        $this->checkTokenVersion($payload, $user);

        return $this->issueTokenPair($user);
    }

    #[\Override]
    public function revoke(string $userId): void
    {
        $user = $this->users->find($userId);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        $user->incrementTokenVersion();
    }

    #[\Override]
    public function verify(string $accessToken): AuthPayload
    {
        $payload = $this->verifyToken($accessToken);

        if (isset($payload['type']) && $payload['type'] !== 'access') {
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

    /**
     * @return array<string, mixed>
     */
    private function verifyToken(string $token): array
    {
        try {
            return $this->tokenGenerator->verify($token);
        } catch (\RuntimeException $e) {
            throw new AuthenticationException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function findUserFromPayload(array $payload): User
    {
        if (!isset($payload['userId']) || !is_string($payload['userId'])) {
            throw new AuthenticationException('Unauthorized');
        }

        $user = $this->users->find($payload['userId']);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new UserNotActiveException();
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function checkTokenVersion(array $payload, User $user): void
    {
        $payloadVersion = isset($payload['tokenVersion']) && is_int($payload['tokenVersion'])
            ? $payload['tokenVersion']
            : 0;

        if ($payloadVersion !== $user->getTokenVersion()) {
            throw new AuthenticationException('Token has been revoked');
        }
    }

    private function issueTokenPair(User $user): TokenPair
    {
        $userId = $user->getId()->getValue();

        $accessToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'access', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenTtlConfig->accessTtl,
        );

        $refreshToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'refresh', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenTtlConfig->refreshTtl,
        );

        return new TokenPair(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $this->tokenTtlConfig->accessTtl,
        );
    }
}
