<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services\Register;

use App\Contexts\Auth\Entities\User;
use App\Contexts\Auth\Forms\RegistrationByEmailForm;
use App\Contexts\Auth\Renders\ConfirmEmailRender;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Components\Mail\Builders\MessageBuilder;
use App\Core\Components\Mail\MailSender;
use App\Core\Components\Security\PasswordHasher;
use App\Core\Components\Tokens\Tokenizer;
use App\Core\Exceptions\UserAlreadyExistException;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;

final readonly class RegistrationByEmailService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private Tokenizer $tokenizer,
        private Flusher $flusher,
        private MailSender $sender,
    ) {
    }

    public function handle(RegistrationByEmailForm $form): void
    {
        $email = new Email($form->email);
        $user = $this->users->findByEmail($email);
        if ($user && $user->isActive()) {
            throw new UserAlreadyExistException();
        }

        $now = new \DateTimeImmutable();
        $token = $this->tokenizer->generate($now);

        if ($user) {
            $this->users->deleteSuccessTokens($user);
            $this->users->setToken($user, $token);
        } else {
            $this->users->add(
                User::createByEmail(
                    id: Id::create(),
                    email: $email,
                    hash: $this->hasher->hash($form->password),
                    token: $token,
                    createdAt: $now
                )
            );
        }

        $this->sender->send(
            MessageBuilder::create()
                ->from((string)env('MAIL_NOREPLY', ''))
                ->to($email->getValue()),
            new ConfirmEmailRender($token)
        );

        $this->flusher->flush();
    }
}
