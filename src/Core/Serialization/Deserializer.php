<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

interface Deserializer
{
    /**
     * @param class-string $class
     */
    public function deserialize(SerializedData $data, string $class): object;
}
