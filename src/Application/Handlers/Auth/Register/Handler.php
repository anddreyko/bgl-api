<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\Register;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\ValueObjects\Email;
use Bgl\Domain\Profile\Entities\EmailConfirmationToken;
use Bgl\Domain\Profile\Entities\EmailConfirmationTokens;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Exceptions\UserAlreadyExistsException;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    private const int TOKEN_TTL_HOURS = 24;

    public function __construct(
        private Users $users,
        private EmailConfirmationTokens $tokens,
        private PasswordHasher $passwordHasher,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        $existing = $this->users->findByEmail($command->email);
        if ($existing !== null) {
            throw new UserAlreadyExistsException();
        }

        $passwordHash = $this->passwordHasher->hash($command->password);
        $now = \DateTimeImmutable::createFromInterface($this->clock->now());

        $user = User::register(
            id: $this->uuidGenerator->generate(),
            email: new Email($command->email),
            passwordHash: $passwordHash,
            createdAt: $now,
            name: $command->name,
        );

        $this->users->add($user);

        $token = EmailConfirmationToken::create(
            id: $this->uuidGenerator->generate(),
            userId: $user->getId(),
            token: $this->uuidGenerator->generate()->getValue() ?? '',
            expiresAt: $now->modify('+' . self::TOKEN_TTL_HOURS . ' hours'),
        );

        $this->tokens->add($token);

        return 'Confirm the specified email';
    }
}
