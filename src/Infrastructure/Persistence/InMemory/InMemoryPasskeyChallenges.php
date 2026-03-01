<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Profile\Passkey\PasskeyChallenge;
use Bgl\Domain\Profile\Passkey\PasskeyChallenges;

/**
 * @extends InMemoryRepository<PasskeyChallenge>
 */
final class InMemoryPasskeyChallenges extends InMemoryRepository implements PasskeyChallenges
{
    #[\Override]
    public function findByChallenge(string $challenge): ?PasskeyChallenge
    {
        foreach ($this->getEntities() as $entity) {
            if ($entity->getChallenge() === $challenge) {
                return $entity;
            }
        }

        return null;
    }
}
