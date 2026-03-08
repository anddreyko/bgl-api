<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class TeamTagTooLongException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Team tag is too long.');
    }
}
