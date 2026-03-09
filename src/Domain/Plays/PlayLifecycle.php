<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

enum PlayLifecycle: string
{
    case Current = 'current';
    case Finished = 'finished';
    case Deleted = 'deleted';
}
