<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\UserTokenConfirm;
use App\Infrastructure\Database\Repositories\DbRepository;

class TokenConfirmRepository extends DbRepository
{
    public function getClass(): string
    {
        return UserTokenConfirm::class;
    }

    public function findUser(string $value): ?UserTokenConfirm
    {
        $token = $this->findOneBy(['token.value' => $value]);

        return $token instanceof UserTokenConfirm ? $token : null;
    }
}
