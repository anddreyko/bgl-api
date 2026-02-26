<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Profile\Entities\PasskeyChallenge;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;

/**
 * @extends InMemoryRepository<PasskeyChallenge>
 */
final class InMemoryPasskeyChallenges extends InMemoryRepository implements PasskeyChallenges
{
    public function __construct()
    {
        parent::__construct(new AnyFieldAccessor());
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

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
