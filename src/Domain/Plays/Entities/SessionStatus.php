<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

enum SessionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Deleted = 'deleted';
}
