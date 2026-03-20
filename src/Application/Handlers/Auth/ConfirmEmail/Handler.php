<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Core\Auth\CredentialType;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Users;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
        private Verifier $verifier,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $credentialType = CredentialType::from($command->type);
        $userId = $this->verifier->confirm($command->credential, $credentialType);

        /** @var string $userIdValue */
        $userIdValue = $userId->getValue();
        $user = $this->users->find($userIdValue);
        if ($user === null) {
            throw new InvalidConfirmationTokenException();
        }

        $user->confirm();

        $tokenPair = $this->tokenIssuer->issue($userIdValue);

        return new Result(
            accessToken: $tokenPair->accessToken,
            refreshToken: $tokenPair->refreshToken,
        );
    }
}
