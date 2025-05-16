<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Core\Exceptions\IncorrectEmailException;
use App\Core\Exceptions\IncorrectPasswordException;
use App\Core\ValueObjects\Email;
use App\Domain\Auth\Exceptions\IdentificationException;
use App\Domain\Auth\Forms\LogInForm;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Database\Flusher;
use App\Infrastructure\Security\PasswordHasher;
use App\Infrastructure\Tokens\JsonWebTokenizer;

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
