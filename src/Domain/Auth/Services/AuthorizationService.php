<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Core\Exceptions\IncorrectTokenException;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Tokens\JsonWebTokenizer;

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
