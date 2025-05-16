<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services\ResetPassword;

use App\Contexts\Auth\Forms\UpdatePasswordForm;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Components\Security\PasswordHasher;

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
