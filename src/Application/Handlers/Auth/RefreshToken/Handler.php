<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RefreshToken;

use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Authenticator $authenticator,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $tokenPair = $this->authenticator->refresh($command->refreshToken);

        return new Result(
            accessToken: $tokenPair->accessToken,
            refreshToken: $tokenPair->refreshToken,
            expiresIn: $tokenPair->expiresIn,
        );
    }
}
