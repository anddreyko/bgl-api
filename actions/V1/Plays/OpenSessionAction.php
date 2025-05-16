<?php

declare(strict_types=1);

namespace Actions\V1\Plays;

use Actions\BaseAction;
use App\Contexts\Plays\Entities\Session;
use App\Contexts\Plays\Repositories\SessionRepository;
use App\Core\Components\Database\Flusher;
use App\Core\Components\Http\Entities\Response;
use App\Core\ValueObjects\Id;

final class OpenSessionAction extends BaseAction
{
    public function __construct(
        private readonly SessionRepository $repository,
        private readonly Flusher $flusher,
    ) {
    }

    public function content(): Response
    {
        $id = Id::create();

        $started = $this->getParam('started_at');
        $startedAt = null;
        if (!empty($started)) {
            try {
                $startedAt = new \DateTimeImmutable($started);
            } catch (\Exception) {
            }
        }
        $startedAt ??= new \DateTimeImmutable();

        $this->repository->create(
            new Session(
                id: $id,
                name: $this->getParam('name') ?: 'Session at ' . $startedAt->format('d.m.Y H:i'),
                startedAt: $startedAt->setTimezone(new \DateTimeZone('UTC'))
            )
        );

        $this->flusher->flush();

        return new Response(['session_id' => $id->getValue()]);
    }
}
