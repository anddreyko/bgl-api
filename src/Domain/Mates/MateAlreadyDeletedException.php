<?php

declare(strict_types=1);

namespace Bgl\Domain\Mates;

final class MateAlreadyDeletedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Mate is already deleted.');
    }
}
