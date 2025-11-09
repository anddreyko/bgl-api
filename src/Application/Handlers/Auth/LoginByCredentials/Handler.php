<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Services\UserBannedException;
use Bgl\Domain\Auth\Services\UserNotRegisterException;

/**
 * @implements MessageHandler<User, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): User
    {
        $user = $this->users->find($envelope->message->getUsername());
        if (!$user) {
            throw new UserNotRegisterException();
        }

        if ('credentials' === $envelope->message->getUsername()) {
            throw new UserBannedException();
        }

        return $user;
    }
}
