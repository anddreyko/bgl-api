<?php

declare(strict_types=1);

namespace App\Auth\Services\Register;

use App\Auth\Entities\User;
use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Helpers\PasswordHashHelper;
use App\Auth\Helpers\TokenizerHelper;
use App\Auth\Renders\ConfirmEmailRender;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Core\Mail\Builders\MessageBuilder;
use App\Core\Mail\Services\MailSenderService;

/**
 * @see \Tests\Unit\Auth\Services\Register\RegistrationByEmailServiceTest
 */
final readonly class RegistrationByEmailService
{
    public function __construct(
        private UserRepository $users,
        private PasswordHashHelper $hasher,
        private TokenizerHelper $tokenizer,
        private FlushHelper $flusher,
        private MailSenderService $sender,
    ) {
    }

    public function handle(RegistrationByEmailForm $form): void
    {
        $email = new Email($form->email);
        if ($this->users->hasByEmail($email)) {
            throw new \DomainException('User with this email has been already exist.');
        }

        $now = new \DateTimeImmutable();
        $token = $this->tokenizer->generate($now);
        $this->users->add(
            User::createByEmail(
                id: Id::create(),
                createdAt: $now,
                email: $email,
                hash: $this->hasher->hash($form->password),
                token: $token
            )
        );

        $this->flusher->flush();
        $this->sender->send(
            MessageBuilder::create()
                ->from(getenv('MAIL_NOREPLY') ?: '')
                ->to($email->getValue()),
            new ConfirmEmailRender($token)
        );
    }
}
