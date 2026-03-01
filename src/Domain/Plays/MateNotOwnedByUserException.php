<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class MateNotOwnedByUserException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Mate does not belong to user.');
    }
}
