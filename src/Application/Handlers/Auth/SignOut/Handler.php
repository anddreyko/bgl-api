<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SignOut;

use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Authenticator $authenticator,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        $this->authenticator->revoke($command->userId);

        return 'sign out';
    }
}
