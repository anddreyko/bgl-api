<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class ColorTooLongException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Color is too long.');
    }
}
