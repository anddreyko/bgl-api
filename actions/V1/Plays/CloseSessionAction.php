<?php

declare(strict_types=1);

namespace Actions\V1\Plays;

use App\Auth\Helpers\FlushHelper;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\ValueObjects\Id;
use App\Plays\Repositories\SessionRepository;

final class CloseSessionAction extends BaseAction
{
    public function __construct(
        private readonly SessionRepository $repository,
        private readonly FlushHelper $flusher
    ) {
    }

    public function content(): Response
    {
        $id = $this->getArgs('id');

        $session = $this->repository->getOneById(new Id($id));

        $started = $this->getParam('started_at');
        $startedAt = null;
        if (!empty($started)) {
            try {
                $startedAt = new \DateTimeImmutable($started);
            } catch (\Exception) {
            }
        }
        if ($startedAt) {
            $session->setStartedAt($startedAt);
        }

        $finished = $this->getParam('finished_at');
        $finishedAt = null;
        if (!empty($finished)) {
            try {
                $finishedAt = (new \DateTimeImmutable($finished))
                    ->setTimezone(new \DateTimeZone('UTC'));
            } catch (\Exception) {
            }
            if ($finishedAt < $session->getStartedAt()) {
                throw new \LogicException(
                    "Incorrect time: start {$session->getStartedAt()->format('Y-m-d H:i:s')}," .
                    " finish {$finishedAt->format('Y-m-d H:i:s')}"
                );
            }
        }

        if (!$finishedAt) {
            $interval = $this->getParam('interval');
            $finishedAt = is_numeric($interval)
                ? $session->getStartedAt()->modify("+ $interval min")
                : new \DateTimeImmutable();
        }

        if ($finishedAt) {
            $session->setFinishedAt($finishedAt);
        }
        $this->repository->persist($session);

        $this->flusher->flush();

        return new Response(
            [
                'session_id' => $session->getId()->getValue(),
                'started_at' => $session->getStartedAt()->format('Y-m-dTH:i:s'),
                'finished_at' => $session->getFinishedAt()?->format('Y-m-dTH:i:s'),
            ]
        );
    }
}
