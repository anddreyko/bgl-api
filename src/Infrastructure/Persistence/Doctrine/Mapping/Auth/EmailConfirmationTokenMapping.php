<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth;

use Bgl\Domain\Profile\Entities\EmailConfirmationToken;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class EmailConfirmationTokenMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return EmailConfirmationToken::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'auth_email_confirmation_token']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'uuid_vo',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'userId',
            'type' => 'uuid_vo',
            'columnName' => 'user_id',
        ]);

        $metadata->mapField([
            'fieldName' => 'token',
            'type' => 'string',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'expiresAt',
            'type' => 'datetime_immutable',
            'columnName' => 'expires_at',
        ]);
    }
}
