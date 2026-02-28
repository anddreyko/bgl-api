<?php

declare(strict_types=1);

namespace Bgl\Core\Collections;

/**
 * @template T of object
 */
interface Collection extends \Countable
{
    /**
     * @param T $element
     */
    public function add(mixed $element): void;

    /**
     * @return array<int, T>
     */
    public function toArray();
}
