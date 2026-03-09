<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\DeletePlay;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\PlayAccessDeniedException;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\PlayLifecycle;

/**
 * @implements MessageHandler<null, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): null
    {
        /** @var Command $command */
        $command = $envelope->message;

        /** @var Play|null $play */
        $play = $this->plays->find((string)$command->sessionId);

        if ($play === null || $play->getLifecycle() === PlayLifecycle::Deleted) {
            throw new NotFoundException('Play not found');
        }

        if ((string)$play->getUserId() !== (string)$command->userId) {
            throw new PlayAccessDeniedException();
        }

        $play->delete();

        return null;
    }
}
