<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RegisterPasskeyOptions;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Passkey\PasskeyChallenge;
use Bgl\Domain\Profile\Passkey\PasskeyChallenges;
use Bgl\Domain\Profile\Users;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    private const int CHALLENGE_TTL_SECONDS = 300;

    public function __construct(
        private Users $users,
        private PasskeyChallenges $challenges,
        private PasskeyVerifier $passkeyVerifier,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $user = $this->users->find($command->userId);
        if ($user === null) {
            throw new AuthenticationException('User not found');
        }

        $userId = $user->getId()->getValue();
        if ($userId === null) {
            throw new AuthenticationException('Unauthorized');
        }

        $this->challenges->removeByUserId($userId);

        $passkeyOptions = $this->passkeyVerifier->registerOptions($userId, $user->getName());

        $challenge = PasskeyChallenge::forRegistration(
            id: $this->uuidGenerator->generate(),
            challenge: $passkeyOptions->challenge,
            expiresAt: new DateTime('+' . self::CHALLENGE_TTL_SECONDS . ' seconds'),
            userId: $user->getId(),
        );

        $this->challenges->add($challenge);

        return new Result(options: $passkeyOptions->optionsJson);
    }
}
