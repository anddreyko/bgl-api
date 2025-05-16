<?php

declare(strict_types=1);

namespace App\Domain\Auth\Forms;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdatePasswordForm
{
    public function __construct(
        #[NotBlank]
        public string $token,
        #[Length(min: 6)]
        public string $password
    ) {
    }
}
