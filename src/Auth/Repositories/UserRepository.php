<?php

declare(strict_types=1);

namespace App\Auth\Repositories;

use App\Auth\Entities\User;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Auth\ValueObjects\WebToken;

interface UserRepository
{
    public function add(User $user): void;

    public function hasByEmail(Email $email): bool;

    public function findByToken(string $token): ?User;

    public function findByEmail(Email $email): ?User;

    public function setToken(User $user, Token $token): void;

    public function activateUser(User $user): void;

    public function getById(Id $id): User;

    public function setPasswordHash(User $user, PasswordHash $hash): void;

    public function addAccessToken(User $user, WebToken $access): void;
}
