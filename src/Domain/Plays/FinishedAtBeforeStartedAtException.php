<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class FinishedAtBeforeStartedAtException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('finishedAt must be after startedAt.');
    }
}
