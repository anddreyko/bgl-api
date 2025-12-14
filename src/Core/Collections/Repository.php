<?php

declare(strict_types=1);

namespace Bgl\Core\Collections;

/**
 * @template TEntity of object
 */
interface Repository
{
    /**
     * @param TEntity $entity
     */
    public function add(object $entity): void;

    /**
     * @param TEntity $entity
     */
    public function remove(object $entity): void;

    /**
     * @param string $id
     *
     * @return TEntity|null
     */
    public function find(string $id): ?object;
}
