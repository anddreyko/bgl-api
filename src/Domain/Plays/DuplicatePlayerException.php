<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

final class DuplicatePlayerException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Duplicate player: same mate cannot be added twice.');
    }
}
