<?php

declare(strict_types=1);

namespace App\Core\Http\Enums;

enum HttpCodesEnum: int
{
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case InternalServerError = 500;
}
