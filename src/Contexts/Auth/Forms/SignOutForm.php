<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Forms;

use App\Contexts\Auth\Entities\User;
use App\Core\ValueObjects\WebToken;
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
