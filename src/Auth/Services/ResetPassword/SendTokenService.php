<?php

declare(strict_types=1);

namespace App\Auth\Services\ResetPassword;

use App\Auth\Forms\SendTokenForm;
use App\Auth\Helpers\FlushHelper;
use App\Auth\Repositories\UserRepository;
use App\Core\Mail\Builders\MessageBuilder;
use App\Core\Mail\Services\MailSenderService;
use App\Core\Tokens\Services\TokenizerService;
use App\Core\ValueObjects\Email;

final readonly class SendTokenService
{
    public function __construct(
        private UserRepository $users,
        private TokenizerService $tokenizer,
        private FlushHelper $flusher,
        private MailSenderService $sender
    ) {
    }

    public function handle(SendTokenForm $form): void
    {
        $email = new Email($form->email);

        $user = $this->users->findByEmail($email);
        if (!$user) {
            throw new \DomainException('User with this email don\'t exist.');
        }

        $token = $this->tokenizer->generate(new \DateTimeImmutable());

        $this->users->setToken($user, $token);

        $this->flusher->flush();

        $this->sender->send(MessageBuilder::create());
    }
}
