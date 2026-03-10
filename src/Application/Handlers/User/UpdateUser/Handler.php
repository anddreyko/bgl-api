<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\UpdateUser;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $this->users->find($command->userId);
        if ($user === null) {
            throw new NotFoundException('User not found');
        }

        $user->rename($command->name);

        return new Result(
            id: (string)$user->getId()->getValue(),
            email: $user->getEmail()->getValue() ?? '',
            isActive: $user->getStatus() === UserStatus::Active,
            createdAt: $user->getCreatedAt()->getFormattedValue(\DateTimeInterface::ATOM),
            name: $user->getName(),
        );
    }
}
