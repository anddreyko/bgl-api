<?php

declare(strict_types=1);

namespace Bgl\Core\Notification;

interface Notifier
{
    /**
     * @throws \RuntimeException on transport failure
     */
    public function send(Notification $notification): void;
}
