<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays;

use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Player\Players;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Bridges Domain Players repository interface with Doctrine ArrayCollection.
 * Doctrine sees ArrayCollection (for OneToMany association),
 * Domain sees Players (Repository<Player>).
 *
 * ParamNameMismatch is expected: Repository uses $entity, Collection uses $element/$key.
 * These interfaces have incompatible param names for the same methods.
 *
 * @extends ArrayCollection<int, Player>
 */
final class PlayerCollection extends ArrayCollection implements Players
{
    #[\Override]
    public function add(mixed $element): void
    {
        /** @var Player $element */
        parent::add($element);
    }

    #[\Override]
    public function remove(string|int|object $key): void
    {
        if ($key instanceof Player) {
            $this->removeElement($key);

            return;
        }

        if (is_int($key)) {
            parent::remove($key);
        }
    }

    #[\Override]
    public function find(string $id): ?object
    {
        /** @var Player $player */
        foreach ($this->toArray() as $player) {
            if ((string)$player->getId() === $id) {
                return $player;
            }
        }

        return null;
    }
}
