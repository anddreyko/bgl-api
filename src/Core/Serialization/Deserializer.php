<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

interface Deserializer
{
    /**
     * @param array<string, mixed> $data
     * @param class-string $class
     */
    public function deserialize(array $data, string $class): object;
}
