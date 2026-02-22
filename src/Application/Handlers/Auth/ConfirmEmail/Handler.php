<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Auth\Entities\EmailConfirmationTokens;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Exceptions\ExpiredConfirmationTokenException;
use Bgl\Domain\Auth\Exceptions\InvalidConfirmationTokenException;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
        private EmailConfirmationTokens $tokens,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        $token = $this->tokens->findByToken($command->token);
        if ($token === null) {
            throw new InvalidConfirmationTokenException();
        }

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        if ($token->isExpired($now)) {
            throw new ExpiredConfirmationTokenException();
        }

        /** @var string $userId */
        $userId = $token->getUserId()->getValue();
        $user = $this->users->find($userId);
        if ($user === null) {
            throw new InvalidConfirmationTokenException();
        }

        $user->confirm();

        $this->tokens->remove($token);

        return 'Specified email is confirmed';
    }
}
