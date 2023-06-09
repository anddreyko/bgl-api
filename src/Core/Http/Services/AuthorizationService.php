<?php

declare(strict_types=1);

namespace App\Core\Http\Services;

use App\Auth\Entities\User;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Id;
use App\Core\Tokens\Services\JsonWebTokenizerService;

final readonly class AuthorizationService
{
    public function __construct(private JsonWebTokenizerService $webTokenizerService, private UserRepository $users)
    {
    }

    public function handle(string $token): User
    {
        /** @var array{user?: string} $payload */
        $payload = $this->webTokenizerService->decode($token);

        return $this->users->getById(new Id($payload['user'] ?? ''));
    }
}
