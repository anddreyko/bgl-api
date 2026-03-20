<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Auth\SendVerification;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Notification\Notifier;
use Bgl\Domain\Profile\Users;
use Bgl\Infrastructure\Notification\SymfonyMailerNotifier;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

return [
    SendVerification\Handler::class => static function (ContainerInterface $c): SendVerification\Handler {
        /** @var Users $users */
        $users = $c->get(Users::class);
        /** @var Verifier $verifier */
        $verifier = $c->get(Verifier::class);
        /** @var Notifier $notifier */
        $notifier = $c->get(Notifier::class);
        /** @var LoggerInterface $logger */
        $logger = $c->get(LoggerInterface::class);
        $frontendUrlEnv = getenv('FRONTEND_URL');
        $frontendUrl = $frontendUrlEnv !== false ? $frontendUrlEnv : 'http://localhost:3000';

        return new SendVerification\Handler($users, $verifier, $notifier, $logger, $frontendUrl);
    },
    Notifier::class => static function (): Notifier {
        $mailerDsn = getenv('MAILER_DSN');
        $dsn = $mailerDsn !== false ? $mailerDsn : 'smtp://localhost:1025';

        $mailFrom = getenv('MAIL_NOREPLY');
        $from = $mailFrom !== false ? $mailFrom : 'noreply@example.com';

        return new SymfonyMailerNotifier(new Mailer(Transport::fromDsn($dsn)), $from);
    },
];
