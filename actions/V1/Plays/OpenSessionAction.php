<?php

declare(strict_types=1);

namespace Actions\V1\Plays;

use App\Auth\Helpers\FlushHelper;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\ValueObjects\Id;
use App\Plays\Entities\Session;
use App\Plays\Repositories\SessionRepository;

final class OpenSessionAction extends BaseAction
{
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

        /** @var SessionRepository $repository */
        $repository = $this->getContainer(SessionRepository::class);
        $repository->create(
            new Session(
                id: $id,
                name: $this->getParam('name') ?: 'Session at ' . $startedAt->format('d.m.Y H:i'),
                startedAt: $startedAt->setTimezone(new \DateTimeZone('UTC'))
            )
        );

        /** @var FlushHelper $flusher */
        $flusher = $this->getContainer(FlushHelper::class);
        $flusher->flush();

        return new Response(['session_id' => $id->getValue()]);
    }
}
