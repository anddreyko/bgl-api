<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

final class PhpMappingDriver implements MappingDriver
{
    /** @var array<class-string, EntityMapping> */
    private array $mappings = [];

    /**
     * @param list<EntityMapping> $mappings
     */
    public function __construct(array $mappings)
    {
        foreach ($mappings as $mapping) {
            $this->mappings[$mapping->getEntityClass()] = $mapping;
        }
    }

    /**
     * @param class-string $className
     */
    #[\Override]
    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
        if (!isset($this->mappings[$className])) {
            throw new \InvalidArgumentException(
                sprintf('No mapping configuration found for class "%s".', $className)
            );
        }

        assert($metadata instanceof OrmClassMetadata);

        /** @var OrmClassMetadata<object> $metadata */
        $this->mappings[$className]->configure($metadata);
    }

    /**
     * @return list<class-string>
     */
    #[\Override]
    public function getAllClassNames(): array
    {
        return array_keys($this->mappings);
    }

    /**
     * @param class-string $className
     */
    #[\Override]
    public function isTransient(string $className): bool
    {
        return !isset($this->mappings[$className]);
    }
}
