<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Passkey;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;

/**
 * @extends Repository<Passkey>
 */
interface Passkeys extends Repository, Searchable
{
    public function findByCredentialId(string $credentialId): ?Passkey;

    /**
     * @return list<Passkey>
     */
    public function findAllByUserId(string $userId): array;
}
