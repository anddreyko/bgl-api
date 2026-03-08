<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class NegativeNumberException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Player number cannot be negative.');
    }
}
