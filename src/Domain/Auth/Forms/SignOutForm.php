<?php

declare(strict_types=1);

namespace App\Domain\Auth\Forms;

use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use Symfony\Component\Validator\Constraints\NotNull;

class SignOutForm
{
    public function __construct(
        #[NotNull]
        public User $user,
        #[NotNull]
        public WebToken $token
    ) {
    }
}
