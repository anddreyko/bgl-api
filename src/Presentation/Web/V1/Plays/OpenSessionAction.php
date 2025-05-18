<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Plays;

use App\Core\ValueObjects\Id;
use App\Domain\Plays\Entities\Session;
use App\Domain\Plays\Repositories\SessionRepository;
use App\Infrastructure\Database\Flusher;
use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;

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

        /** @var ?string $started */
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
        $startedAt ??= new \DateTimeImmutable();

        $this->repository->create(
            new Session(
                id: $id,
                name: (string)$this->getParam('name') ?: 'Session at ' . $startedAt->format('d.m.Y H:i'),
                startedAt: $startedAt->setTimezone(new \DateTimeZone('UTC'))
            )
        );

        $this->flusher->flush();

        return new Response(['session_id' => $id->getValue()]);
    }
}
