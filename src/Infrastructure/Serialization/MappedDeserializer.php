<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Serialization;

use Bgl\Core\Serialization\Deserializer;
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
    public function deserialize(array $data, string $class): object
    {
        $factory = $this->mapping[$class] ?? null;
        if ($factory !== null) {
            /** @var object */
            return $factory($data);
        }

        return $this->hydrator->hydrateObject($class, $data);
    }
}
