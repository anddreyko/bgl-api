<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Authentification\OpenAuth;

use Bgl\Core\Auth\Identities;
use Bgl\Core\Auth\Identity;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\Users as UserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

final readonly class Users implements UserRepositoryInterface, Identities
{
    public function __construct(private UserRepository $users)
    {
    }

    #[\Override]
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        // Для Passkey аутентификации пароль не требуется
        if ($grantType === 'passkey') {
            $user = $this->users->find($username);

            return $user ? new UserId($user->id) : null;
        }

        $results = $this->users->search(
            filter: new AndX([new Equals('login', $username), new Equals('email', $password)]),
            size: new PageSize(1)
        );

        /** @var array<string, mixed>|false $firstResult */
        $firstResult = current($results);
        if ($firstResult === false || !isset($firstResult['id'])) {
            return null;
        }

        /** @var non-empty-string $userId */
        $userId = $firstResult['id'];

        return new UserId(new Uuid($userId));
    }

    #[\Override]
    public function findByCredentials(string $username, string $password): ?Identity
    {
        $results = $this->users->search(
            filter: new AndX([new Equals('login', $username), new Equals('password', $password)]),
            size: new PageSize(1)
        );

        /** @var array<string, mixed>|false $firstResult */
        $firstResult = current($results);
        if ($firstResult === false || !isset($firstResult['id'])) {
            return null;
        }

        /** @var non-empty-string $userId */
        $userId = $firstResult['id'];

        return new Identity(new Uuid($userId));
    }

    #[\Override]
    public function findById(string $id): ?Identity
    {
        $user = $this->users->find($id);

        return $user !== null ? new Identity($user->id) : null;
    }
}
