<?php

declare(strict_types=1);

use Bgl\Application\Handlers;
use Bgl\Core\ValueObjects;

return [
    ValueObjects\Date::class => static fn(ValueObjects\Date $model) => [
        'timestamp' => $model->getNullableFormattedValue('c'),
        'date' => $model->getNullableFormattedValue('Y-m-d'),
    ],
    ValueObjects\DateTime::class => static fn(ValueObjects\DateTime $model) => [
        'timestamp' => $model->getNullableFormattedValue('c'),
        'datetime' => $model->getNullableFormattedValue(DATE_W3C),
    ],
    ValueObjects\DateInterval::class => static fn(ValueObjects\DateInterval $model) => [
        'seconds' => $model->getSeconds(),
        'interval' => $model->getIso(),
    ],
    Handlers\Ping\Result::class => static fn(Handlers\Ping\Result $model) => [
        'message_id' => $model->messageId,
        'parent_id' => $model->parentId,
        'trace_id' => $model->traceId,
        'environment' => $model->environment,
        'version' => $model->version,
        'datetime' => $model->datetime->isNull() ? null : $model->datetime,
        'delay' => $model->delay ? [
            'seconds' => $model->delay->getSeconds(),
            'interval' => $model->delay->getIso(),
        ] : null,
    ],
    Handlers\Auth\LoginByCredentials\Result::class => static fn(Handlers\Auth\LoginByCredentials\Result $model) => [
        'access_token' => $model->accessToken,
        'refresh_token' => $model->refreshToken,
        'expires_in' => $model->expiresIn,
    ],
    Handlers\Auth\RefreshToken\Result::class => static fn(Handlers\Auth\RefreshToken\Result $model) => [
        'access_token' => $model->accessToken,
        'refresh_token' => $model->refreshToken,
        'expires_in' => $model->expiresIn,
    ],
];
