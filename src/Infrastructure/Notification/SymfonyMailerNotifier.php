<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Notification;

use Bgl\Core\Notification\Notification;
use Bgl\Core\Notification\Notifier;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyMailerNotifier implements Notifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $defaultFrom,
    ) {
    }

    #[\Override]
    public function send(Notification $notification): void
    {
        $email = new Email()
            ->from($notification->from ?? $this->defaultFrom)
            ->to($notification->to)
            ->subject($notification->subject)
            ->text($notification->body);

        $this->mailer->send($email);
    }
}
