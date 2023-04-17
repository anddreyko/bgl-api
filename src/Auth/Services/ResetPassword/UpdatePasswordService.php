<?php

declare(strict_types=1);

namespace App\Auth\Services\ResetPassword;

use App\Auth\Forms\UpdatePasswordForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Repositories\UserRepository;

final readonly class UpdatePasswordService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private FlushHelper $flusher
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
