<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services\ResetPassword;

use App\Core\ValueObjects\Email;
use App\Domain\Auth\Forms\SendTokenForm;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Database\Flusher;
use App\Infrastructure\Mail\Builders\MessageBuilder;
use App\Infrastructure\Mail\MailSender;
use App\Infrastructure\Tokens\Tokenizer;

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
