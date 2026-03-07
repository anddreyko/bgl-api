<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

enum HttpCode: int
{
    case Ok = 200;
    case Created = 201;
    case NoContent = 204;
    case MultipleChoices = 300;
    case BadRequest = 400;
    case Unauthorized = 401;
    case NotFound = 404;
    case ValidationError = 422;
    case InternalServerError = 500;
}
