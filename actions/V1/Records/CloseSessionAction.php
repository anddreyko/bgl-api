<?php

declare(strict_types=1);

namespace Actions\V1\Records;

use App\Auth\Helpers\FlushHelper;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\ValueObjects\Id;
use App\Records\Repositories\SessionRepository;

final class CloseSessionAction extends BaseAction
{
    public function content(): Response
    {
        $id = $this->getArgs('id');

        /** @var SessionRepository $repository */
        $repository = $this->getContainer(SessionRepository::class);
        $session = $repository->getOneById(new Id($id));

        $session->setFinishedAt(new \DateTimeImmutable());
        $repository->persist($session);

        /** @var FlushHelper $flusher */
        $flusher = $this->getContainer(FlushHelper::class);
        $flusher->flush();

        return new Response(
            [
                'session_id' => $session->getId()->getValue(),
                'started_at' => $session->getStartedAt()->format('Y-m-dTH:i:s'),
                'finished_at' => $session->getFinishedAt()?->format('Y-m-dTH:i:s'),
            ]
        );
    }
}
