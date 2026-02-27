<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

interface Denormalizer
{
    /**
     * @param array<string, string> $mapping  Field mapping configuration
     * @param list<string>          $required Required field names
     *
     * @return array<string, string|int|null>|null null if required fields missing
     */
    public function denormalize(mixed $source, array $mapping, array $required = []): ?array;
}
