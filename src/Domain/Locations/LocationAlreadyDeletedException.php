<?php

declare(strict_types=1);

namespace Bgl\Domain\Locations;

final class LocationAlreadyDeletedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Location is already deleted.');
    }
}
