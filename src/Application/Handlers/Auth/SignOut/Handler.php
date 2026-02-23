<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SignOut;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Auth\Entities\Users;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $this->users->find($command->userId);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        $user->incrementTokenVersion();

        return 'sign out';
    }
}
