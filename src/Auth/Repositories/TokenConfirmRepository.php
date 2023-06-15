<?php

declare(strict_types=1);

namespace App\Auth\Repositories;

use App\Auth\Entities\UserTokenConfirm;
use App\Core\Database\Repositories\DbRepository;

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
