<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Fields;

interface FieldAccessor
{
    public function get(array|object $entity, int|string $field): mixed;
}
