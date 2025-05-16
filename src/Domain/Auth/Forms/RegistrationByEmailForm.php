<?php

declare(strict_types=1);

namespace App\Domain\Auth\Forms;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

final readonly class RegistrationByEmailForm
{
    public function __construct(
        #[Email]
        public string $email,
        #[Length(min: 6)]
        public string $password
    ) {
    }
}
