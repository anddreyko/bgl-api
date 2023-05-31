<?php

declare(strict_types=1);

namespace App\Core\Http\Enums;

/**
 * @see \Tests\Unit\Core\Http\Enums\HttpCodesEnumTest
 */
enum HttpCodesEnum: int
{
    case Success = 200;
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case Conflict = 409;
    case Gone = 410;
    case UnprocessableEntity = 422;
    case InternalServerError = 500;
    case NotImplemented = 501;

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success.',
            self::BadRequest => 'Bad request.',
            self::Unauthorized => 'Unauthorized.',
            self::Forbidden => 'Forbidden.',
            self::NotFound => 'Not found.',
            self::MethodNotAllowed => 'Method not allowed.',
            self::Conflict => 'Conflict.',
            self::Gone => 'Gone.',
            self::UnprocessableEntity => 'Unprocessable entity.',
            self::InternalServerError => 'Unexpected error.',
            self::NotImplemented => 'Not implemented.',
        };
    }
}
