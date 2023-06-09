<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Exceptions\IdentificationException;
use App\Auth\Exceptions\IncorrectEmailException;
use App\Auth\Exceptions\IncorrectPasswordException;
use App\Auth\Forms\IdentificationForm;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Core\Tokens\Services\JsonWebTokenizerService;

final readonly class IdentificationService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private JsonWebTokenizerService $webTokenizerService
    ) {
    }

    public function handle(IdentificationForm $form): string
    {
        $email = new Email($form->email);
        $user = $this->users->findByEmail($email);
        if (!$user) {
            throw new IdentificationException(previous: new IncorrectEmailException());
        }

        $hash = $user->getHash();
        if (!$hash) {
            throw new IdentificationException(previous: new IncorrectPasswordException());
        }

        if (!$this->hasher->validate($form->password, $hash)) {
            throw new IdentificationException(previous: new IncorrectPasswordException());
        }

        return $this->webTokenizerService->encode(['user' => $user->getId()]);
    }
}
