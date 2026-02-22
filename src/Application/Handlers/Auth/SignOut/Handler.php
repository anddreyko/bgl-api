<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SignOut;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;

/**
 * @implements MessageHandler<string, Command>
 */
final readonly class Handler implements MessageHandler
{
    #[\Override]
    public function __invoke(Envelope $envelope): string
    {
        /** @var Command $command */
        $command = $envelope->message;

        // MVP: sign-out is client-side (token deletion)
        // Server simply acknowledges the request
        return 'sign out';
    }
}
