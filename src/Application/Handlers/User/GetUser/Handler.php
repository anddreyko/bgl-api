<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\User\GetUser;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\UserResolver;
use Bgl\Domain\Profile\UserStatus;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private UserResolver $userResolver,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        $user = $this->userResolver->resolve($query->userId);
        if ($user === null) {
            throw new NotFoundException('User not found');
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
