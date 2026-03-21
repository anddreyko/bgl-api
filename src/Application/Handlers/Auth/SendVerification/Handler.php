<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SendVerification;

use Bgl\Core\Auth\TooManyRequestsException;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Notification\Notification;
use Bgl\Core\Notification\Notifier;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Domain\Profile\Users;
use Psr\Log\LoggerInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Users $users,
        private Verifier $verifier,
        private Notifier $notifier,
        private LoggerInterface $logger,
        private string $frontendUrl,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $command->userId !== null
            ? $this->users->find($command->userId)
            : $this->users->findByEmail($command->email);

        if ($user === null) {
            return new Result(message: 'Verification request accepted');
        }

        if ($user->getStatus() === UserStatus::Active) {
            return new Result(message: 'Verification request accepted');
        }

        if (!$this->verifier->canIssue($user->getId())) {
            throw new TooManyRequestsException();
        }

        $credentials = $this->verifier->issue($user->getId());

        try {
            $this->notifier->send(new Notification(
                to: $command->email,
                subject: 'Verify your MeepleJournal account',
                body: "Your verification code: {$credentials->code}\n"
                    . "The code is valid for 15 minutes.\n\n"
                    . "Or follow the link: {$this->frontendUrl}/verify?token={$credentials->token}",
            ));
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to send verification email', [
                'email' => $command->email,
                'error' => $e->getMessage(),
            ]);
        }

        return new Result(message: 'Verification request accepted');
    }
}
