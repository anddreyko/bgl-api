<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Forms\SignOutForm;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Database\Flusher;

final readonly class SignOutService
{
    public function __construct(
        private UserRepository $users,
        private Flusher $flusher,
    ) {
    }

    public function handle(SignOutForm $form): void
    {
        $user = $this->users->getById($form->user->getId());
        $this->users->deleteAccessToken($user, $form->token);

        $this->flusher->flush();
    }
}
