<?php

declare(strict_types=1);

namespace App\Auth\Services\Register;

use App\Auth\Entities\User;
use App\Auth\Exceptions\UserAlreadyExistException;
use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Renders\ConfirmEmailRender;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Core\Mail\Builders\MessageBuilder;
use App\Core\Mail\Services\MailSenderService;
use App\Core\Tokens\Services\TokenizerService;

/**
 * @see \Tests\Unit\Auth\Services\Register\RegistrationByEmailServiceTest
 */
final readonly class RegistrationByEmailService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private TokenizerService $tokenizer,
        private FlushHelper $flusher,
        private MailSenderService $sender,
    ) {
    }

    public function handle(RegistrationByEmailForm $form): void
    {
        $email = new Email($form->email);
        if ($this->users->hasByEmail($email)) {
            throw new UserAlreadyExistException();
        }

        $now = new \DateTimeImmutable();
        $token = $this->tokenizer->generate($now);
        $this->users->add(
            User::createByEmail(
                id: Id::create(),
                email: $email,
                hash: $this->hasher->hash($form->password),
                token: $token,
                createdAt: $now
            )
        );

        $this->sender->send(
            MessageBuilder::create()
                ->from((string)env('MAIL_NOREPLY', ''))
                ->to($email->getValue()),
            new ConfirmEmailRender($token)
        );

        $this->flusher->flush();
    }
}
