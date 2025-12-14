<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class UserMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return User::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'auth_user']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'guid',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'email',
            'type' => 'string',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'createdAt',
            'type' => 'date_immutable',
        ]);

        $metadata->mapField([
            'fieldName' => 'status',
            'type' => 'string',
        ]);
    }
}
