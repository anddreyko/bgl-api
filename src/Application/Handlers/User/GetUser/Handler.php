<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\GetUser;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Auth\Entities\Users;

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
            throw new \DomainException('User not found');
        }

        return new Result(
            id: (string)$user->getId()->getValue(),
            email: '',
            isActive: $user->getStatus() === \Bgl\Domain\Auth\Entities\UserStatus::Active,
            createdAt: $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
