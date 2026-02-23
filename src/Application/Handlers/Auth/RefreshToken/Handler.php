<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RefreshToken;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\UserNotActiveException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private TokenGenerator $tokenGenerator,
        private Users $users,
        private TokenTtlConfig $tokenTtlConfig,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $payload = $this->tokenGenerator->verify($command->refreshToken);

        if (!isset($payload['userId']) || !is_string($payload['userId'])) {
            throw new InvalidRefreshTokenException();
        }

        if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
            throw new InvalidRefreshTokenException();
        }

        $user = $this->users->find($payload['userId']);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new UserNotActiveException();
        }

        $payloadVersion = isset($payload['tokenVersion']) && is_int($payload['tokenVersion'])
            ? $payload['tokenVersion']
            : 0;

        if ($payloadVersion !== $user->getTokenVersion()) {
            throw new AuthenticationException('Token has been revoked');
        }

        $userId = $user->getId()->getValue();

        $accessToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'access', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenTtlConfig->accessTtl,
        );

        $refreshToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'refresh', 'tokenVersion' => $user->getTokenVersion()],
            $this->tokenTtlConfig->refreshTtl,
        );

        return new Result(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $this->tokenTtlConfig->accessTtl,
        );
    }
}
