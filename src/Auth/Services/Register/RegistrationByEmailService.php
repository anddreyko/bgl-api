<?php

declare(strict_types=1);

namespace App\Auth\Services\Register;

use App\Auth\Entities\User;
use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Helpers\SendMailHelper;
use App\Auth\Helpers\TokenizerHelper;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;

final readonly class RegistrationByEmailService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private TokenizerHelper $tokenizer,
        private FlushHelper $flusher,
        private SendMailHelper $sender,
    ) {
    }

    public function handle(RegistrationByEmailForm $form): void
    {
        $email = new Email($form->email);
        if ($this->users->hasByEmail($email)) {
            throw new \DomainException('User with this email has been already exist.');
        }

        $now = new \DateTimeImmutable();
        $this->users->add(
            User::createByEmail(
                id: Id::create(),
                date: $now,
                email: $email,
                hash: $this->hasher->hash($form->password),
                token: $this->tokenizer->generate($now)
            )
        );

        $this->flusher->flush();
        $this->sender->send();
    }
}
