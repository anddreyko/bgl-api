<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class NegativeScoreException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Score cannot be negative.');
    }
}
