<?php

declare(strict_types=1);

namespace App\Auth\Forms;

use App\Auth\Entities\User;
use App\Auth\ValueObjects\WebToken;
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
