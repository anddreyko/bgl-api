<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Forms\SignOutForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Repositories\UserRepository;

final readonly class SignOutService
{
    public function __construct(
        private UserRepository $users,
        private FlushHelper $flusher,
    ) {
    }

    public function handle(SignOutForm $form): void
    {
        $user = $this->users->getById($form->user->getId());
        $this->users->deleteAccessToken($user, $form->token);

        $this->flusher->flush();
    }
}
