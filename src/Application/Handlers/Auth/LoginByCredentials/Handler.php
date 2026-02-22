<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\PasswordHasher;
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
        private Users $users,
        private PasswordHasher $passwordHasher,
        private TokenGenerator $tokenGenerator,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $this->users->findByEmail($command->email);
        if ($user === null) {
            throw new \DomainException('Invalid credentials');
        }

        if (!$this->passwordHasher->verify($command->password, $user->getPasswordHash())) {
            throw new \DomainException('Invalid credentials');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new \DomainException('Email not confirmed');
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
