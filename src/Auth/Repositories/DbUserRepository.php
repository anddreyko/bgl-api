<?php

declare(strict_types=1);

namespace App\Auth\Repositories;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Repositories\DbRepository;

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
        $user = $this->findOneBy(['token.value' => $token]);
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
        $user->setToken($token);
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
            throw new \DomainException("User #{$id->getValue()} not found.");
        }

        return $user;
    }

    public function setPasswordHash(User $user, PasswordHash $hash): void
    {
        $user->setHash($hash);
        $this->persist($user);
    }
}
