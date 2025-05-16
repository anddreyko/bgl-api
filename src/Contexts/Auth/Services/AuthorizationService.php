<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services;

use App\Contexts\Auth\Entities\User;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Tokens\JsonWebTokenizer;
use App\Core\Exceptions\IncorrectTokenException;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\WebToken;

final readonly class AuthorizationService
{
    public function __construct(private JsonWebTokenizer $webTokenizerService, private UserRepository $users)
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
