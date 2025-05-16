<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services\Register;

use App\Core\Exceptions\IncorrectTokenException;
use App\Domain\Auth\Exceptions\ExpiredTokenException;
use App\Domain\Auth\Forms\ConfirmationEmailForm;
use App\Domain\Auth\Repositories\TokenConfirmRepository;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Database\Flusher;

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
