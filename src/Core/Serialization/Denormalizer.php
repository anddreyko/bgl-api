<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

interface Denormalizer
{
    /**
     * @return DenormalizedData|null null if required fields missing
     */
    public function denormalize(
        mixed $source,
        FieldMapping $mapping,
        RequiredFields $required = new RequiredFields(),
    ): ?DenormalizedData;
}
