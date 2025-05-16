<?php

declare(strict_types=1);

namespace App\Domain\Auth\Forms;

use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class ConfirmationEmailForm
{
    public function __construct(
        #[NotBlank]
        public string $token
    ) {
    }
}
