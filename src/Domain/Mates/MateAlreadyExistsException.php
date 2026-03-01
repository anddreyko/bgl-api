<?php

declare(strict_types=1);

namespace Bgl\Domain\Mates;

final class MateAlreadyExistsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Mate with this name already exists.');
    }
}
