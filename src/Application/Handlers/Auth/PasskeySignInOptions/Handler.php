<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\PasskeySignInOptions;

use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Entities\PasskeyChallenge;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    private const int CHALLENGE_TTL_SECONDS = 300;

    public function __construct(
        private PasskeyChallenges $challenges,
        private PasskeyVerifier $passkeyVerifier,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        $passkeyOptions = $this->passkeyVerifier->loginOptions();

        $challenge = PasskeyChallenge::forLogin(
            id: $this->uuidGenerator->generate(),
            challenge: $passkeyOptions->challenge,
            expiresAt: new DateTime('+' . self::CHALLENGE_TTL_SECONDS . ' seconds'),
        );

        $this->challenges->add($challenge);

        return new Result(options: $passkeyOptions->optionsJson);
    }
}
