<?php

declare(strict_types=1);

namespace App\Domain\Auth\Forms;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class LogInForm
{
    public function __construct(
        #[Email]
        public string $email,
        #[Length(min: 6)]
        public string $password
    ) {
    }
}
