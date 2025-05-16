<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services\Register;

use App\Contexts\Auth\Exceptions\ExpiredTokenException;
use App\Contexts\Auth\Forms\ConfirmationEmailForm;
use App\Contexts\Auth\Repositories\TokenConfirmRepository;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Exceptions\IncorrectTokenException;

final readonly class ConfirmationEmailService
{
    public function __construct(
        private UserRepository $users,
        private TokenConfirmRepository $tokens,
        private Flusher $flusher
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
