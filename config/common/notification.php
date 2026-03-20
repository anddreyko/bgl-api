<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Auth\SendVerification;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Notification\Notifier;
use Bgl\Domain\Profile\Users;
use Bgl\Infrastructure\Notification\PhpMailerNotifier;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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

        /** @var array{host?: string, port?: int} $parts */
        $parts = parse_url($dsn);
        $host = $parts['host'] ?? 'localhost';
        $port = $parts['port'] ?? 1025;

        $mailFrom = getenv('MAIL_NOREPLY');
        $from = $mailFrom !== false ? $mailFrom : 'noreply@example.com';

        return new PhpMailerNotifier($host, $port, $from);
    },
];
