<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services\ResetPassword;

use App\Domain\Auth\Forms\UpdatePasswordForm;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Database\Flusher;
use App\Infrastructure\Security\PasswordHasher;

final readonly class UpdatePasswordService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private Flusher $flusher
    ) {
    }

    public function handle(UpdatePasswordForm $form): void
    {
        $user = $this->users->findByToken($form->token);
        if (!$user) {
            throw new \DomainException('Token is incorrect or expired');
        }

        $this->users->setPasswordHash($user, $this->hasher->hash($form->password));

        $this->flusher->flush();
    }
}
