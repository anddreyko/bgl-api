<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Authentification\OpenAuth;

use Bgl\Core\Auth\Authentificator;
use Bgl\Core\Auth\GrantNotSupportedException;
use Bgl\Core\Auth\GrantType;
use Bgl\Core\Auth\Identities;
use Bgl\Core\Auth\Identity;
use Bgl\Core\Auth\InvalidCredentialsException;

final readonly class LeagueAuthServer implements Authentificator
{
    private const array SUPPORTED_GRANTS = [
        GrantType::Credential,
        GrantType::Passkey,
    ];

    public function __construct(
        private Identities $identities,
    ) {
    }

    #[\Override]
    public function authenticate(GrantType $grant, array $credentials): Identity
    {
        if (!$this->supports($grant)) {
            throw new GrantNotSupportedException(
                sprintf('Grant type "%s" is not supported', $grant->value)
            );
        }

        return match ($grant) {
            GrantType::Credential => $this->authenticateByCredentials($credentials),
            GrantType::Passkey => $this->authenticateByPasskey($credentials),
        };
    }

    #[\Override]
    public function supports(GrantType $grant): bool
    {
        return in_array($grant, self::SUPPORTED_GRANTS, true);
    }

    /**
     * @param array<string, mixed> $credentials
     */
    private function authenticateByCredentials(array $credentials): Identity
    {
        $username = isset($credentials['username']) && \is_string($credentials['username'])
            ? $credentials['username']
            : '';
        $password = isset($credentials['password']) && \is_string($credentials['password'])
            ? $credentials['password']
            : '';

        if ($username === '' || $password === '') {
            throw new InvalidCredentialsException('Username and password are required');
        }

        $identity = $this->identities->findByCredentials($username, $password);

        if ($identity === null) {
            throw new InvalidCredentialsException();
        }

        return $identity;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    private function authenticateByPasskey(array $credentials): Identity
    {
        $userId = isset($credentials['userId']) && \is_string($credentials['userId'])
            ? $credentials['userId']
            : '';

        if ($userId === '') {
            throw new InvalidCredentialsException('User ID is required for passkey authentication');
        }

        $identity = $this->identities->findById($userId);

        if ($identity === null) {
            throw new InvalidCredentialsException();
        }

        return $identity;
    }
}
