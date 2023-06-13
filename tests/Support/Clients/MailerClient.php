<?php

declare(strict_types=1);

namespace Tests\Support\Clients;

use GuzzleHttp\Client;

final class MailerClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => env('MAILER_URL')]);
    }

    public function delete(): void
    {
        $this->client->delete('/api/v1/messages');
    }

    public function hasMessage(string $to): bool
    {
        $res = $this->client->get('/api/v2/search?kind=to&query=' . urlencode($to));
        /** @var array{total: int} $data */
        $data = json_decode((string)$res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $data['total'] > 0;
    }
}
