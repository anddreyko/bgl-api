<?php

declare(strict_types=1);

namespace App\Plays\Enums;

enum SessionStatus: int
{
    case Draft = 0;
    case Published = 1;
    case Deleted = 2;
}
