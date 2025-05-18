<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Plays;

use App\Core\ValueObjects\Id;
use App\Domain\Plays\Repositories\SessionRepository;
use App\Infrastructure\Database\Flusher;
use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;

final class CloseSessionAction extends BaseAction
{
    public function __construct(
        private readonly SessionRepository $repository,
        private readonly Flusher $flusher
    ) {
    }

    public function content(): Response
    {
        $id = $this->getArgs('id');

        $session = $this->repository->getOneById(new Id($id));

        /** @var string|null $started */
        $started = $this->getParam('started_at');
        if (is_string($started)) {
            $started = trim($started);
        }

        $startedAt = null;
        if (null !== $started && '' !== $started) {
            try {
                $startedAt = new \DateTimeImmutable($started);
            } catch (\Exception) {
            }
        }
        if ($startedAt) {
            $session->setStartedAt($startedAt);
        }

        /** @var string|null $finished */
        $finished = $this->getParam('finished_at');
        if (is_string($finished)) {
            $finished = trim($finished);
        }

        $finishedAt = null;
        if (null !== $finished && '' !== $finished) {
            try {
                $finishedAt = (new \DateTimeImmutable($finished))
                    ->setTimezone(new \DateTimeZone('UTC'));
            } catch (\Exception) {
            }
            if ($finishedAt instanceof \DateTimeImmutable && $finishedAt < $session->getStartedAt()) {
                throw new \LogicException(
                    "Incorrect time: start {$session->getStartedAt()->format('Y-m-d H:i:s')}," .
                    " finish {$finishedAt->format('Y-m-d H:i:s')}"
                );
            }
        }

        if (!$finishedAt) {
            /** @var ?int $interval */
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
