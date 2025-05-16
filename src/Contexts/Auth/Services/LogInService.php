<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services;

use App\Contexts\Auth\Exceptions\IdentificationException;
use App\Contexts\Auth\Forms\LogInForm;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Components\Security\PasswordHasher;
use App\Core\Components\Tokens\JsonWebTokenizer;
use App\Core\Exceptions\IncorrectEmailException;
use App\Core\Exceptions\IncorrectPasswordException;
use App\Core\ValueObjects\Email;

final readonly class LogInService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private JsonWebTokenizer $webTokenizerService,
        private Flusher $flusher,
    ) {
    }

    public function handle(LogInForm $form): string
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

        $access = $this->webTokenizerService->encode(['user' => $user->getId()->getValue()], expire: '+2 days');

        $this->users->addAccessToken($user, $access);

        $this->flusher->flush();

        return $access->getValue();
    }
}
