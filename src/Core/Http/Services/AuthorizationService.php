<?php

declare(strict_types=1);

namespace App\Core\Http\Services;

use App\Auth\Entities\User;
use App\Auth\Exceptions\IncorrectTokenException;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\WebToken;
use App\Core\Tokens\Services\JsonWebTokenizerService;

final readonly class AuthorizationService
{
    public function __construct(private JsonWebTokenizerService $webTokenizerService, private UserRepository $users)
    {
    }

    public function handle(WebToken $token): User
    {
        /** @var array{user?: string} $payload */
        $payload = $this->webTokenizerService->decode($token);

        $user = $this->users->getById(new Id($payload['user'] ?? ''));

        if (!$this->users->hasTokenAccess($user, $token)) {
            throw new IncorrectTokenException();
        }

        return $user;
    }
}
