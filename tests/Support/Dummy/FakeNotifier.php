<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Dummy;

use Bgl\Core\Notification\Notification;
use Bgl\Core\Notification\Notifier;

final class FakeNotifier implements Notifier
{
    /** @var list<Notification> */
    private array $sent = [];

    #[\Override]
    public function send(Notification $notification): void
    {
        $this->sent[] = $notification;
    }

    /**
     * @return list<Notification>
     */
    public function getSent(): array
    {
        return $this->sent;
    }

    public function getLastSent(): ?Notification
    {
        return $this->sent[\count($this->sent) - 1] ?? null;
    }

    public function reset(): void
    {
        $this->sent = [];
    }
}
