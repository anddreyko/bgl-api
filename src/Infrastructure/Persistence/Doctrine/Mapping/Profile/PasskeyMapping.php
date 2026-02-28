<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile;

use Bgl\Domain\Profile\Entities\Passkey;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\EntityMapping;
use Doctrine\ORM\Mapping\ClassMetadata;

final class PasskeyMapping implements EntityMapping
{
    #[\Override]
    public function getEntityClass(): string
    {
        return Passkey::class;
    }

    #[\Override]
    public function configure(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'auth_passkey']);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'uuid_vo', 'id' => true]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->configureFields($metadata);
    }

    private function configureFields(ClassMetadata $metadata): void
    {
        $metadata->mapField(['fieldName' => 'userId', 'type' => 'uuid_vo', 'columnName' => 'user_id']);
        $metadata->mapField(
            ['fieldName' => 'credentialId', 'type' => 'string', 'columnName' => 'credential_id', 'unique' => true]
        );
        $metadata->mapField(['fieldName' => 'credentialData', 'type' => 'text', 'columnName' => 'credential_data']);
        $metadata->mapField(['fieldName' => 'counter', 'type' => 'integer', 'options' => ['default' => 0]]);
        $metadata->mapField(['fieldName' => 'createdAt', 'type' => 'datetime_immutable', 'columnName' => 'created_at']);
        $metadata->mapField(['fieldName' => 'label', 'type' => 'string', 'nullable' => true]);
    }
}
