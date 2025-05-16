<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Repositories;

use App\Contexts\Auth\Entities\User;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Core\ValueObjects\WebToken;

interface UserRepository
{
    public function add(User $user): void;

    public function hasByEmail(Email $email): bool;

    public function findByToken(string $token): ?User;

    public function findByEmail(Email $email): ?User;

    public function setToken(User $user, Token $token): void;

    public function deleteSuccessToken(User $user, Token $token): void;

    public function deleteSuccessTokens(User $user): void;

    public function activateUser(User $user): void;

    public function getById(Id $id): User;

    public function setPasswordHash(User $user, PasswordHash $hash): void;

    public function addAccessToken(User $user, WebToken $access): void;

    public function deleteAccessToken(User $user, WebToken $access): void;

    public function hasTokenAccess(User $user, WebToken $access): bool;
}
