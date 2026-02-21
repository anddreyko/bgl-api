<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Fields;

final readonly class AnyFieldAccessor implements FieldAccessor
{
    #[\Override]
    public function get(object|array $entity, int|string $field): mixed
    {
        if (\is_array($entity)) {
            if (\array_key_exists($field, $entity)) {
                return $entity[$field];
            }

            throw FieldDoesNotExistException::arrayKey($entity, $field);
        }

        $property = (string)$field;

        try {
            $reflectionProperty = new \ReflectionProperty($entity, $property);
        } catch (\ReflectionException $exception) {
            throw FieldDoesNotExistException::property($entity::class, $property, $exception);
        }

        return $reflectionProperty->getValue($entity);
    }
}
