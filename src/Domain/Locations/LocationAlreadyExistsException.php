<?php

declare(strict_types=1);

namespace Bgl\Domain\Locations;

final class LocationAlreadyExistsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Location with this name already exists.');
    }
}
