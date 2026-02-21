<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

interface Serializer
{
    public function serialize(object $data): array;
}
