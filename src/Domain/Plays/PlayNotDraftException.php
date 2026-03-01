<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class PlayNotDraftException extends \DomainException
{
    public function __construct(string $message = 'Play is not in draft status.')
    {
        parent::__construct($message);
    }
}
