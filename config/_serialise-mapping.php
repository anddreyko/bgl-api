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
        'datetime' => !$model->datetime->isNull() ? [
            'timestamp' => $model->datetime->getNullableFormattedValue('U'),
            'datetime' => $model->datetime->getNullableFormattedValue(DATE_W3C),
        ] : null,
        'delay' => !$model->delay->isNull() ? [
            'seconds' => $model->delay->getSeconds(),
            'interval' => $model->delay->getIso(),
        ] : null,
    ],
    Handlers\Auth\LoginByCredentials\Result::class => static fn(Handlers\Auth\LoginByCredentials\Result $model) => [
        'access_token' => $model->accessToken,
        'refresh_token' => $model->refreshToken,
    ],
    Handlers\Auth\RefreshToken\Result::class => static fn(Handlers\Auth\RefreshToken\Result $model) => [
        'access_token' => $model->accessToken,
        'refresh_token' => $model->refreshToken,
    ],
    Handlers\User\GetUser\Result::class => static fn(Handlers\User\GetUser\Result $model) => [
        'id' => $model->id,
        'email' => $model->email,
        'name' => $model->name,
        'is_active' => $model->isActive,
        'created_at' => $model->createdAt,
    ],
    Handlers\Plays\CreatePlay\Result::class => static fn(Handlers\Plays\CreatePlay\Result $model) => [
        'id' => $model->id,
        'author' => $model->author,
        'name' => $model->name,
        'status' => $model->status,
        'visibility' => $model->visibility,
        'started_at' => $model->startedAt,
        'finished_at' => $model->finishedAt,
        'game' => $model->game,
        'players' => $model->players,
    ],
    Handlers\Plays\UpdatePlay\Result::class => static fn(Handlers\Plays\UpdatePlay\Result $model) => [
        'id' => $model->id,
        'author' => $model->author,
        'name' => $model->name,
        'status' => $model->status,
        'visibility' => $model->visibility,
        'started_at' => $model->startedAt,
        'finished_at' => $model->finishedAt,
        'game' => $model->game,
        'players' => $model->players,
    ],
    Handlers\Plays\FinalizePlay\Result::class => static fn(Handlers\Plays\FinalizePlay\Result $model) => [
        'id' => $model->id,
        'author' => $model->author,
        'name' => $model->name,
        'status' => $model->status,
        'visibility' => $model->visibility,
        'started_at' => $model->startedAt,
        'finished_at' => $model->finishedAt,
        'game' => $model->game,
        'players' => $model->players,
    ],
    Handlers\Plays\GetPlay\Result::class => static fn(Handlers\Plays\GetPlay\Result $model) => [
        'id' => $model->id,
        'author' => $model->author,
        'name' => $model->name,
        'status' => $model->status,
        'visibility' => $model->visibility,
        'started_at' => $model->startedAt,
        'finished_at' => $model->finishedAt,
        'game' => $model->game,
        'players' => $model->players,
    ],
    Handlers\Mates\CreateMate\Result::class => static fn(Handlers\Mates\CreateMate\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'notes' => $model->notes,
        'created_at' => $model->createdAt,
    ],
    Handlers\Mates\GetMate\Result::class => static fn(Handlers\Mates\GetMate\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'notes' => $model->notes,
        'is_system' => $model->isSystem,
        'created_at' => $model->createdAt,
    ],
    Handlers\Mates\UpdateMate\Result::class => static fn(Handlers\Mates\UpdateMate\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'notes' => $model->notes,
        'created_at' => $model->createdAt,
    ],
    Handlers\Mates\ListMates\Result::class => static fn(Handlers\Mates\ListMates\Result $model) => [
        'items' => $model->data,
        'total' => $model->total,
        'page' => $model->page,
        'size' => $model->size,
    ],
    Handlers\Plays\ListPlays\Result::class => static fn(Handlers\Plays\ListPlays\Result $model) => [
        'items' => $model->data,
        'total' => $model->total,
        'page' => $model->page,
        'size' => $model->size,
    ],
    Handlers\Games\GetGame\Result::class => static fn(Handlers\Games\GetGame\Result $model) => [
        'id' => $model->id,
        'bgg_id' => $model->bggId,
        'name' => $model->name,
        'year_published' => $model->yearPublished,
    ],
    Handlers\Games\SearchGames\Result::class => static fn(Handlers\Games\SearchGames\Result $model) => [
        'items' => $model->data,
        'total' => $model->total,
        'page' => $model->page,
        'size' => $model->size,
    ],
    Handlers\Auth\ConfirmEmail\Result::class => static fn(Handlers\Auth\ConfirmEmail\Result $model) => [
        'message' => $model->message,
    ],
    Handlers\Auth\Register\Result::class => static fn(Handlers\Auth\Register\Result $model) => [
        'message' => $model->message,
    ],
    Handlers\Auth\RegisterPasskeyVerify\Result::class => static fn(Handlers\Auth\RegisterPasskeyVerify\Result $model
    ) => [
        'message' => $model->message,
    ],
    Handlers\Auth\SignOut\Result::class => static fn(Handlers\Auth\SignOut\Result $model) => [
        'message' => $model->message,
    ],
    Handlers\Auth\PasskeySignInOptions\Result::class => static fn(Handlers\Auth\PasskeySignInOptions\Result $model) => [
        'options' => $model->options,
    ],
    Handlers\Auth\PasskeySignInVerify\Result::class => static fn(Handlers\Auth\PasskeySignInVerify\Result $model) => [
        'access_token' => $model->accessToken,
        'refresh_token' => $model->refreshToken,
    ],
    Handlers\Auth\RegisterPasskeyOptions\Result::class => static fn(Handlers\Auth\RegisterPasskeyOptions\Result $model
    ) => [
        'options' => $model->options,
    ],
    Handlers\Locations\CreateLocation\Result::class => static fn(Handlers\Locations\CreateLocation\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'address' => $model->address,
        'notes' => $model->notes,
        'url' => $model->url,
        'created_at' => $model->createdAt,
    ],
    Handlers\Locations\GetLocation\Result::class => static fn(Handlers\Locations\GetLocation\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'address' => $model->address,
        'notes' => $model->notes,
        'url' => $model->url,
        'created_at' => $model->createdAt,
    ],
    Handlers\Locations\UpdateLocation\Result::class => static fn(Handlers\Locations\UpdateLocation\Result $model) => [
        'id' => $model->id,
        'name' => $model->name,
        'address' => $model->address,
        'notes' => $model->notes,
        'url' => $model->url,
        'created_at' => $model->createdAt,
    ],
    Handlers\Locations\ListLocations\Result::class => static fn(Handlers\Locations\ListLocations\Result $model) => [
        'items' => $model->data,
        'total' => $model->total,
        'page' => $model->page,
        'size' => $model->size,
    ],
];
