<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Authentification\OpenAuth;

use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users as UserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

final readonly class Users implements UserRepositoryInterface
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

        /** @var ?User $user */
        $user = current(
            $this->users->search(
                filter: new AndX([new Equals('login', $username), new Equals('email', $password)]),
                size: new PageSize(1)
            )
        ) ?: null;

        return $user ? new UserId($user->id) : null;
    }
}
