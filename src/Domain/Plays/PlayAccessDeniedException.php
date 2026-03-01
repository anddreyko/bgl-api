<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class PlayAccessDeniedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Access denied.');
    }
}
