<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Tests\Support\Clients\MailerClient;

trait MailerHelper
{
    private ?MailerClient $client = null;

    private function cleanMails(): void
    {
        $this->mailer()->delete();
    }

    private function mailer(): MailerClient
    {
        if (!$this->client) {
            $this->client = new MailerClient();
        }

        return $this->client;
    }

    private function checkMails(string $to): bool
    {
        return $this->mailer()->hasMessage($to);
    }
}
