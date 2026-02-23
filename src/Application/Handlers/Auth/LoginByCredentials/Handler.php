<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Core\Auth\EmailNotConfirmedException;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\PasswordHasher;
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
        private Users $users,
        private PasswordHasher $passwordHasher,
        private TokenGenerator $tokenGenerator,
        private TokenTtlConfig $tokenTtlConfig,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $this->users->findByEmail($command->email);
        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        if (!$this->passwordHasher->verify($command->password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException();
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new EmailNotConfirmedException();
        }

        $userId = $user->getId()->getValue();

        $accessToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'access'],
            $this->tokenTtlConfig->accessTtl,
        );

        $refreshToken = $this->tokenGenerator->generate(
            ['userId' => $userId, 'type' => 'refresh'],
            $this->tokenTtlConfig->refreshTtl,
        );

        return new Result(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: $this->tokenTtlConfig->accessTtl,
        );
    }
}
