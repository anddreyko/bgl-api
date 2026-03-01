<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Serialization;

use Bgl\Core\Serialization\Deserializer;
use Bgl\Core\Serialization\SerializedData;
use EventSauce\ObjectHydrator\ObjectMapper;

final readonly class MappedDeserializer implements Deserializer
{
    /**
     * @param array<class-string, callable(array<string, mixed>): object> $mapping
     */
    public function __construct(
        private ObjectMapper $hydrator,
        private array $mapping,
    ) {
    }

    #[\Override]
    public function deserialize(SerializedData $data, string $class): object
    {
        $dataArray = $data->toArray();
        $factory = $this->mapping[$class] ?? null;
        if ($factory !== null) {
            /** @var object */
            return $factory($dataArray);
        }

        return $this->hydrator->hydrateObject($class, $dataArray);
    }
}
