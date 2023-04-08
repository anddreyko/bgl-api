<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Helpers\SendMailHelper;
use App\Auth\Helpers\TokenizerHelper;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;

final readonly class RegisterByEmailService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private TokenizerHelper $tokenizer,
        private FlushHelper $flusher,
        private SendMailHelper $sender,
    ) {
    }

    public function run(RegistrationByEmailForm $form): void
    {
        $email = new Email($form->email);
        if ($this->users->hasByEmail($email)) {
            throw new \DomainException('This user already register.');
        }

        $now = new \DateTimeImmutable();
        $this->users->add(
            new User(
                Id::create(),
                $now,
                $email,
                $this->hasher->hash($form->password),
                $this->tokenizer->generate($now),
                UserStatusEnum::wait()
            )
        );

        $this->flusher->flush();
        $this->sender->send();
    }
}
