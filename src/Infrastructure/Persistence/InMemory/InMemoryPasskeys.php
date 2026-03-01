<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Profile\Passkey\Passkey;
use Bgl\Domain\Profile\Passkey\Passkeys;

/**
 * @extends InMemoryRepository<Passkey>
 */
final class InMemoryPasskeys extends InMemoryRepository implements Passkeys
{
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
    public function findAllByUserId(string $userId): iterable
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
