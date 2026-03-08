<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Locations\CreateLocation;
use Bgl\Application\Handlers\Locations\DeleteLocation;
use Bgl\Application\Handlers\Locations\GetLocation;
use Bgl\Application\Handlers\Locations\ListLocations;
use Bgl\Application\Handlers\Locations\UpdateLocation;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/locations' => [
                'post' => [
                    'summary' => 'Create location',
                    'operationId' => 'createLocation',
                    'tags' => ['Locations'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => CreateLocation\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['name'],
                                    'properties' => [
                                        'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                                        'address' => ['type' => 'string', 'maxLength' => 255],
                                        'notes' => ['type' => 'string', 'maxLength' => 500],
                                        'url' => ['type' => 'string', 'maxLength' => 500],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => ['$ref' => '#/components/responses/Location'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'get' => [
                    'summary' => 'List locations',
                    'operationId' => 'listLocations',
                    'tags' => ['Locations'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => ListLocations\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
                        ],
                        [
                            'name' => 'size',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20],
                        ],
                        [
                            'name' => 'sort',
                            'in' => 'query',
                            'schema' => ['type' => 'string', 'enum' => ['name', 'createdAt'], 'default' => 'name'],
                        ],
                        [
                            'name' => 'order',
                            'in' => 'query',
                            'schema' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'default' => 'asc'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful operation',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'code' => ['type' => 'integer', 'example' => 0],
                                            'data' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'items' => [
                                                        'type' => 'array',
                                                        'items' => ['$ref' => '#/components/schemas/Location'],
                                                    ],
                                                    'total' => ['type' => 'integer'],
                                                    'page' => ['type' => 'integer'],
                                                    'size' => ['type' => 'integer'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/locations/{id}' => [
                'get' => [
                    'summary' => 'Get location',
                    'operationId' => 'getLocation',
                    'tags' => ['Locations'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => GetLocation\Query::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'locationId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Location'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'put' => [
                    'summary' => 'Update location',
                    'operationId' => 'updateLocation',
                    'tags' => ['Locations'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => UpdateLocation\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'locationId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['name'],
                                    'properties' => [
                                        'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                                        'address' => ['type' => 'string', 'maxLength' => 255],
                                        'notes' => ['type' => 'string', 'maxLength' => 500],
                                        'url' => ['type' => 'string', 'maxLength' => 500],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/Location'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
                'delete' => [
                    'summary' => 'Delete location',
                    'operationId' => 'deleteLocation',
                    'tags' => ['Locations'],
                    'security' => [['BearerAuth' => []]],
                    'x-message' => DeleteLocation\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'x-map' => ['id' => 'locationId'],
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '204' => ['description' => 'Location deleted'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
