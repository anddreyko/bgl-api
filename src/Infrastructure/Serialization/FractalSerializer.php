<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Serialization;

use Bgl\Core\Serialization\SerializedData;
use Bgl\Core\Serialization\Serializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

final readonly class FractalSerializer implements Serializer
{
    /**
     * @param array<class-string, \Closure> $transformer
     */
    public function __construct(
        private Manager $manager,
        private array $transformer,
    ) {
    }

    #[\Override]
    public function serialize(object $data): SerializedData
    {
        /** @var callable|null $callback */
        $callback = $this->transformer[$data::class] ?? null;

        /** @var array<string, mixed> $result */
        $result = $this->manager->createData(
            new Item(
                data: $data,
                transformer: $callback,
            )
        )->toArray()['data'] ?? [];

        return SerializedData::fromArray($result);
    }
}
