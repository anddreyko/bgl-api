<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\Register;

use Bgl\Application\Handlers\Auth\SendVerification;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\Hasher;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\UserAlreadyExistsException;
use Bgl\Domain\Profile\Users;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
        private Dispatcher $dispatcher,
        private Hasher $passwordHasher,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $existing = $this->users->findByEmail($command->email);
        if ($existing !== null) {
            throw new UserAlreadyExistsException();
        }

        $passwordHash = $this->passwordHasher->hash($command->password);
        $now = new DateTime($this->clock->now());

        $user = User::register(
            id: $this->uuidGenerator->generate(),
            email: new Email($command->email),
            passwordHash: $passwordHash,
            createdAt: $now,
            name: $command->name,
        );

        $this->users->add($user);

        $this->dispatcher->dispatch(
            new SendVerification\Command($command->email),
            $envelope,
        );

        return new Result(message: 'Confirm the specified email');
    }
}
