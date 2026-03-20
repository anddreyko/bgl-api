<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Auth\SendVerification;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Notification\Notifier;
use Bgl\Domain\Profile\Users;
use Bgl\Tests\Support\Dummy\FakeNotifier;
use Bgl\Tests\Support\Dummy\FakeVerifier;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    FakeNotifier::class => static fn(): FakeNotifier => new FakeNotifier(),
    FakeVerifier::class => static fn(): FakeVerifier => new FakeVerifier(),
    Notifier::class => static fn(FakeNotifier $n): Notifier => $n,
    Verifier::class => static fn(FakeVerifier $v): Verifier => $v,
    SendVerification\Handler::class => static function (ContainerInterface $c): SendVerification\Handler {
        /** @var Users $users */
        $users = $c->get(Users::class);
        /** @var Verifier $verifier */
        $verifier = $c->get(Verifier::class);
        /** @var Notifier $notifier */
        $notifier = $c->get(Notifier::class);
        /** @var LoggerInterface $logger */
        $logger = $c->get(LoggerInterface::class);

        return new SendVerification\Handler($users, $verifier, $notifier, $logger, (string)getenv('FRONTEND_URL'));
    },
];
