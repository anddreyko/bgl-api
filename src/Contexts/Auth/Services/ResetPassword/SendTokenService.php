<?php

declare(strict_types=1);

namespace App\Contexts\Auth\Services\ResetPassword;

use App\Contexts\Auth\Forms\SendTokenForm;
use App\Contexts\Auth\Repositories\UserRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Components\Mail\Builders\MessageBuilder;
use App\Core\Components\Mail\MailSender;
use App\Core\Components\Tokens\Tokenizer;
use App\Core\ValueObjects\Email;

final readonly class SendTokenService
{
    public function __construct(
        private UserRepository $users,
        private Tokenizer $tokenizer,
        private Flusher $flusher,
        private MailSender $sender
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
