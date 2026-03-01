<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\GetUser;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Profile\UserStatus;

/**
 * @implements MessageHandler<Result, Query>
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
        /** @var Query $query */
        $query = $envelope->message;

        $user = $this->users->find($query->userId);
        if ($user === null) {
            throw new \Bgl\Core\Exceptions\NotFoundException('User not found');
        }

        return new Result(
            id: (string)$user->getId()->getValue(),
            email: $user->getEmail()->getValue() ?? '',
            isActive: $user->getStatus() === UserStatus::Active,
            createdAt: $user->getCreatedAt()->getFormattedValue(\DateTimeInterface::ATOM),
            name: $user->getName(),
        );
    }
}
