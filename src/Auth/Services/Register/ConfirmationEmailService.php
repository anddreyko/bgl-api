<?php

declare(strict_types=1);

namespace App\Auth\Services\Register;

use App\Auth\Forms\ConfirmationEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Repositories\UserRepository;

final readonly class ConfirmationEmailService
{
    public function __construct(private UserRepository $users, private FlushHelper $flusher)
    {
    }

    public function handle(ConfirmationEmailForm $form): void
    {
        $user = $this->users->findByToken($form->token);
        if (!$user) {
            throw new \DomainException('Incorrect token.');
        }

        if (!$user->getToken()?->validate($form->token)) {
            throw new \DomainException('This token has been expired.');
        }

        $this->users->activateUser($user);

        $this->flusher->flush();
    }
}
