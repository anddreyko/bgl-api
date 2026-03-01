<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RegisterPasskeyVerify;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Passkey\Passkey;
use Bgl\Domain\Profile\Passkey\PasskeyChallenges;
use Bgl\Domain\Profile\Passkey\Passkeys;
use Psr\Clock\ClockInterface;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private PasskeyChallenges $challenges,
        private Passkeys $passkeys,
        private PasskeyVerifier $passkeyVerifier,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $challenge = $this->findChallengeForUser($command->userId);

        $result = $this->passkeyVerifier->register($command->response, $challenge->getChallenge());

        $userId = $command->userId;
        if ($userId === '') {
            throw new AuthenticationException('Unauthorized');
        }

        $passkey = Passkey::create(
            id: $this->uuidGenerator->generate(),
            userId: new Uuid($userId),
            credentialId: $result->credentialId,
            credentialData: $result->credentialData,
            createdAt: new DateTime($this->clock->now()),
            label: $command->label,
        );

        $this->passkeys->add($passkey);
        $this->challenges->remove($challenge);

        return new Result(message: 'ok');
    }

    private function findChallengeForUser(string $userId): \Bgl\Domain\Profile\Passkey\PasskeyChallenge
    {
        $challenges = $this->challenges;
        /** @var iterable<array<string, mixed>> $results */
        $results = $challenges->search(All::Filter);

        // Find a challenge that belongs to this user and is not expired
        foreach ($results as $row) {
            /** @var string $id */
            $id = (string)($row['id'] ?? '');
            $entity = $challenges->find($id);
            if ($entity === null) {
                continue;
            }

            $challengeUserId = $entity->getUserId();
            if ($challengeUserId !== null && (string)$challengeUserId === $userId) {
                if ($entity->isExpired(new DateTime($this->clock->now()))) {
                    $this->challenges->remove($entity);
                    throw new AuthenticationException('Challenge expired');
                }

                return $entity;
            }
        }

        throw new AuthenticationException('No challenge found');
    }
}
