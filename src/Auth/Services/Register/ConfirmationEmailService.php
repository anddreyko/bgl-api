<?php

declare(strict_types=1);

namespace App\Auth\Services\Register;

use App\Auth\Exceptions\ExpiredTokenException;
use App\Auth\Exceptions\IncorrectTokenException;
use App\Auth\Forms\ConfirmationEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Repositories\TokenConfirmRepository;
use App\Auth\Repositories\UserRepository;

final readonly class ConfirmationEmailService
{
    public function __construct(
        private UserRepository $users,
        private TokenConfirmRepository $tokens,
        private FlushHelper $flusher
    ) {
    }

    public function handle(ConfirmationEmailForm $form): void
    {
        $tokenConfirm = $this->tokens->findUser($form->token);
        if (!$tokenConfirm) {
            throw new IncorrectTokenException();
        }

        $token = $tokenConfirm->getToken();
        $user = $tokenConfirm->getUser();
        if ($token->isExpire()) {
            $this->users->deleteSuccessToken($user, $token);
            throw new ExpiredTokenException();
        }

        $this->users->activateUser($user);
        $this->users->deleteSuccessTokens($user);

        $this->flusher->flush();
    }
}
