<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Profile\Entities\Passkey;
use Bgl\Domain\Profile\Entities\Passkeys;

/**
 * @extends InMemoryRepository<Passkey>
 */
final class InMemoryPasskeys extends InMemoryRepository implements Passkeys
{
    public function __construct()
    {
        parent::__construct(new AnyFieldAccessor());
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByCredentialId(string $credentialId): ?Passkey
    {
        foreach ($this->getEntities() as $passkey) {
            if ($passkey->getCredentialId() === $credentialId) {
                return $passkey;
            }
        }

        return null;
    }

    #[\Override]
    public function findAllByUserId(string $userId): array
    {
        $result = [];
        foreach ($this->getEntities() as $passkey) {
            if ((string)$passkey->getUserId() === $userId) {
                $result[] = $passkey;
            }
        }

        return $result;
    }
}
