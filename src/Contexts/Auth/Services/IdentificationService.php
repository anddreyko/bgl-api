<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services;

use App\Contexts\Auth\Forms\IdentificationForm;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Security\PasswordHasher;
use App\Core\Exceptions\IncorrectEmailException;
use App\Core\Exceptions\IncorrectPasswordException;
use App\Core\ValueObjects\Email;

final readonly class IdentificationService
{
    public function __construct(private UserRepository $users, private PasswordHasher $hasher)
    {
    }

    public function handle(IdentificationForm $form): void
    {
        $email = new Email($form->email);
        $user = $this->users->findByEmail($email);
        if (!$user) {
            throw new IncorrectEmailException();
        }

        $hash = $user->getHash();
        if (!$hash) {
            throw new IncorrectPasswordException();
        }

        if (!$this->hasher->validate($form->password, $hash)) {
            throw new IncorrectPasswordException();
        }
    }
}
