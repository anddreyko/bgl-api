<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth;

use Bgl\Domain\Profile\Entities\PasskeyChallenge;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class PasskeyChallengeMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return PasskeyChallenge::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'auth_passkey_challenge']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'uuid_vo',
            'id' => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $metadata->mapField([
            'fieldName' => 'challenge',
            'type' => 'string',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'expiresAt',
            'type' => 'datetime_immutable',
            'columnName' => 'expires_at',
        ]);

        $metadata->mapField([
            'fieldName' => 'userId',
            'type' => 'uuid_vo',
            'columnName' => 'user_id',
            'nullable' => true,
        ]);
    }
}
