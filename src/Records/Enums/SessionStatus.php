<?php

declare(strict_types=1);

namespace App\Records\Enums;

enum SessionStatus: int
{
    case Draft = 0;
    case Published = 1;
    case Deleted = 2;
}
