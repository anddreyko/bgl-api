<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;

interface EntityMapping
{
    /**
     * @return class-string
     */
    public function getEntityClass(): string;

    /**
     * @param ClassMetadata<object> $metadata
     */
    public function configure(ClassMetadata $metadata): void;
}
