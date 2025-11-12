<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\MessageBus\Tactician;

use Bgl\Core\Messages\Envelope;
use League\Tactician\Handler\CommandNameExtractor\CommandNameExtractor;

final class TacticianCommandNameExtractor implements CommandNameExtractor
{
    #[\Override]
    public function extract($command): string
    {
        if ($command instanceof Envelope) {
            return $command->message::class;
        }

        return $command::class;
    }
}
