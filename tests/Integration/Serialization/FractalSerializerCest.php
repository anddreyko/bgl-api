<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Serialization;

use Bgl\Core\Serialization\Serializer;
use Bgl\Infrastructure\Serialization\FractalSerializer;
use Bgl\Tests\Support\DiHelper;
use Codeception\Attribute\Group;

#[Group('core', 'serialization', 'serializer')]
final class FractalSerializerCest extends BaseSerializer
{
    #[\Override]
    protected function serializer(): Serializer
    {
        return DiHelper::container()->get(FractalSerializer::class);
    }
}
