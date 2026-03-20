<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Notification;

use Bgl\Core\Notification\Notification;
use Bgl\Core\Notification\Notifier;
use PHPMailer\PHPMailer\PHPMailer;

final readonly class PhpMailerNotifier implements Notifier
{
    public function __construct(
        private string $host,
        private int $port,
        private string $defaultFrom,
    ) {
    }

    #[\Override]
    public function send(Notification $notification): void
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $this->host;
        $mailer->Port = $this->port;
        $mailer->SMTPAuth = false;
        $mailer->setFrom($notification->from ?? $this->defaultFrom);
        $mailer->addAddress($notification->to);
        $mailer->Subject = $notification->subject;
        $mailer->Body = $notification->body;
        $mailer->send();
    }
}
