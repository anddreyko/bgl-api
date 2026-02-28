<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Core\Collections\ArrayCollection;
use Bgl\Domain\Plays\Entities\Play;
use Bgl\Domain\Plays\Entities\Player;
use Bgl\Domain\Plays\Entities\Plays as PlayRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineArrayCollection;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Play>
 */
final class Plays extends DoctrineRepository implements PlayRepository
{
    #[\Override]
    public function getType(): string
    {
        return Play::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'p';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function add(object $entity): void
    {
        $this->convertPlayersCollection($entity);

        parent::add($entity);
    }

    /**
     * Converts Core ArrayCollection to Doctrine-compatible DoctrineArrayCollection
     * so that Doctrine ORM can manage the OneToMany association.
     */
    private function convertPlayersCollection(Play $play): void
    {
        $ref = new \ReflectionProperty(Play::class, 'players');

        /** @var ArrayCollection<Player>|object $current */
        $current = $ref->getValue($play);

        if ($current instanceof ArrayCollection) {
            /** @var DoctrineArrayCollection<Player> $doctrine */
            $doctrine = new DoctrineArrayCollection();
            /** @var Player $player */
            foreach ($current->toArray() as $player) {
                $doctrine->add($player);
            }
            $ref->setValue($play, $doctrine);
        }
    }
}
