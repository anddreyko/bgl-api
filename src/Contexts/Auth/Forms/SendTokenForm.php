<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Forms;

use Symfony\Component\Validator\Constraints\Email;

class SendTokenForm
{
    public function __construct(
        #[Email]
        public string $email
    ) {
    }
}
