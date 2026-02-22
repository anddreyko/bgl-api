<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\Register;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\EmailConfirmationToken;
use Bgl\Domain\Auth\Entities\EmailConfirmationTokens;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Exceptions\UserAlreadyExistsException;
use Psr\Clock\ClockInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;

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
            id: new Uuid(RamseyUuid::uuid4()->toString()),
            email: new Email(),
            passwordHash: $passwordHash,
            createdAt: $now,
        );

        $this->users->add($user);

        $token = EmailConfirmationToken::create(
            id: new Uuid(RamseyUuid::uuid4()->toString()),
            userId: $user->getId(),
            token: RamseyUuid::uuid4()->toString(),
            expiresAt: $now->modify('+' . self::TOKEN_TTL_HOURS . ' hours'),
        );

        $this->tokens->add($token);

        return 'Confirm the specified email';
    }
}
