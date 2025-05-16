<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services;

use App\Contexts\Auth\Forms\SignOutForm;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;

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
