<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Auth;

use Bgl\Infrastructure\Auth\VerificationToken;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class VerificationTokenMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return VerificationToken::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'auth_verification_token']);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'uuid_vo', 'id' => true]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->mapField(['fieldName' => 'userId', 'type' => 'uuid_vo', 'columnName' => 'user_id']);
        $metadata->mapField(['fieldName' => 'codeHash', 'type' => 'string', 'columnName' => 'code_hash']);
        $metadata->mapField(['fieldName' => 'token', 'type' => 'string', 'unique' => true]);
        $metadata->mapField(['fieldName' => 'expiresAt', 'type' => 'datetime_immutable', 'columnName' => 'expires_at']);
        $metadata->mapField(['fieldName' => 'attemptCount', 'type' => 'integer', 'columnName' => 'attempt_count']);
        $metadata->mapField(['fieldName' => 'createdAt', 'type' => 'datetime_immutable', 'columnName' => 'created_at']);
    }
}
