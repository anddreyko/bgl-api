<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Core\Exceptions\NotFoundException;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Enums\UserStatusEnum;
use App\Infrastructure\Database\Repositories\DbRepository;

final class DbUserRepository extends DbRepository implements UserRepository
{
    public function getClass(): string
    {
        return User::class;
    }

    public function add(User $user): void
    {
        $this->persist($user);
    }

    public function hasByEmail(Email $email): bool
    {
        return (bool)$this->count(['email' => $email->getValue()]);
    }

    public function findByToken(string $token): ?User
    {
        $user = $this->findOneBy(['tokenConfirm' => $token]);
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        $user = $this->findOneBy(['email' => $email->getValue()]);
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function setToken(User $user, Token $token): void
    {
        $user->setTokenConfirm($token);
        $this->persist($user);
    }

    public function deleteSuccessToken(User $user, Token $token): void
    {
        $user->removeTokenConfirm($token);
        $this->persist($user);
    }

    public function deleteSuccessTokens(User $user): void
    {
        $user->removeTokenConfirm();
        $this->persist($user);
    }

    public function activateUser(User $user): void
    {
        $user->setStatus(UserStatusEnum::Active);
        $this->persist($user);
    }

    public function getById(Id $id): User
    {
        $user = $this->findOneBy(['id' => $id->getValue()]);
        if (!($user instanceof User)) {
            throw new NotFoundException("User #{$id->getValue()} not found.");
        }

        return $user;
    }

    public function setPasswordHash(User $user, PasswordHash $hash): void
    {
        $user->setHash($hash);
        $this->persist($user);
    }

    public function addAccessToken(User $user, WebToken $access): void
    {
        $user->setTokenAccess($access);
        $this->persist($user);
    }

    public function deleteAccessToken(User $user, WebToken $access): void
    {
        foreach ($user->getTokenAccess() as $key => $tokenAccess) {
            if ($tokenAccess->getValue() === $access->getValue()) {
                $user->removeTokenAccess($key);
            }
        }

        $this->persist($user);
    }

    public function hasTokenAccess(User $user, WebToken $access): bool
    {
        foreach ($user->getTokenAccess() as $tokenAccess) {
            if ($tokenAccess->getValue() === $access->getValue()) {
                return true;
            }
        }

        return false;
    }
}
