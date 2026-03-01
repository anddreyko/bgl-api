<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Users;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
        private Confirmer $confirmer,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        $userId = $this->confirmer->confirm($command->token);

        /** @var string $userIdValue */
        $userIdValue = $userId->getValue();
        $user = $this->users->find($userIdValue);
        if ($user === null) {
            throw new InvalidConfirmationTokenException();
        }

        $user->confirm();

        return 'Specified email is confirmed';
    }
}
