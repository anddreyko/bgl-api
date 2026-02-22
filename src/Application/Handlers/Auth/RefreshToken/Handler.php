<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RefreshToken;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    private const int ACCESS_TOKEN_TTL = 7200;
    private const int REFRESH_TOKEN_TTL = 2592000;

    public function __construct(
        private TokenGenerator $tokenGenerator,
        private Users $users,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $payload = $this->tokenGenerator->verify($command->refreshToken);

        if (!isset($payload['userId']) || !is_string($payload['userId'])) {
            throw new \DomainException('Invalid refresh token');
        }

        if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
            throw new \DomainException('Invalid token type');
        }

        $user = $this->users->find($payload['userId']);
        if ($user === null) {
            throw new \DomainException('User not found');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new \DomainException('User is not active');
        }

        $userId = $user->getId()->getValue();

        $accessToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'access'],
            self::ACCESS_TOKEN_TTL,
        );

        $refreshToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'refresh'],
            self::REFRESH_TOKEN_TTL,
        );

        return new Result(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: self::ACCESS_TOKEN_TTL,
        );
    }
}
