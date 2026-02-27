<?php

declare(strict_types=1);

use Bgl\Application\Handlers\Auth\ConfirmEmail;
use Bgl\Application\Handlers\Auth\LoginByCredentials;
use Bgl\Application\Handlers\Auth\PasskeySignInOptions;
use Bgl\Application\Handlers\Auth\PasskeySignInVerify;
use Bgl\Application\Handlers\Auth\RefreshToken;
use Bgl\Application\Handlers\Auth\Register;
use Bgl\Application\Handlers\Auth\RegisterPasskeyOptions;
use Bgl\Application\Handlers\Auth\RegisterPasskeyVerify;
use Bgl\Application\Handlers\Auth\SignOut;
use Bgl\Presentation\Api\Interceptors\AuthInterceptor;

return [
    'openapi' => [
        'paths' => [
            '/v1/auth/sign-up' => [
                'post' => [
                    'summary' => 'Register new user',
                    'operationId' => 'registerUser',
                    'tags' => ['Auth'],
                    'x-message' => Register\Command::class,
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                        'password' => ['type' => 'string', 'minLength' => 8],
                                        'name' => ['type' => 'string', 'maxLength' => 100],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/StringSuccess'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/sign-in' => [
                'post' => [
                    'summary' => 'Login with credentials',
                    'operationId' => 'loginByCredentials',
                    'tags' => ['Auth'],
                    'x-message' => LoginByCredentials\Command::class,
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                        'password' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/TokenPairSuccess'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/confirm/{token}' => [
                'get' => [
                    'summary' => 'Confirm email',
                    'operationId' => 'confirmEmail',
                    'tags' => ['Auth'],
                    'x-message' => ConfirmEmail\Command::class,
                    'parameters' => [
                        [
                            'name' => 'token',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/StringSuccess'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/refresh' => [
                'post' => [
                    'summary' => 'Refresh token pair',
                    'operationId' => 'refreshToken',
                    'tags' => ['Auth'],
                    'x-message' => RefreshToken\Command::class,
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['refreshToken'],
                                    'properties' => [
                                        'refreshToken' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/TokenPairSuccess'],
                        '400' => ['$ref' => '#/components/responses/BadRequest'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/sign-out' => [
                'post' => [
                    'summary' => 'Sign out',
                    'operationId' => 'signOut',
                    'tags' => ['Auth'],
                    'x-message' => SignOut\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'security' => [['BearerAuth' => []]],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/StringSuccess'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/passkey/register' => [
                'post' => [
                    'summary' => 'Get passkey registration options',
                    'operationId' => 'registerPasskeyOptions',
                    'tags' => ['Auth'],
                    'x-message' => RegisterPasskeyOptions\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'security' => [['BearerAuth' => []]],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/PasskeyOptionsSuccess'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/passkey/register/verify' => [
                'post' => [
                    'summary' => 'Verify passkey registration',
                    'operationId' => 'registerPasskeyVerify',
                    'tags' => ['Auth'],
                    'x-message' => RegisterPasskeyVerify\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                    'security' => [['BearerAuth' => []]],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['response'],
                                    'properties' => [
                                        'response' => ['type' => 'string'],
                                        'label' => ['type' => 'string', 'maxLength' => 255],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/StringSuccess'],
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/passkey/sign-in' => [
                'post' => [
                    'summary' => 'Get passkey sign-in options',
                    'operationId' => 'passkeySignInOptions',
                    'tags' => ['Auth'],
                    'x-message' => PasskeySignInOptions\Command::class,
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/PasskeyOptionsSuccess'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
            '/v1/auth/passkey/sign-in/verify' => [
                'post' => [
                    'summary' => 'Verify passkey sign-in',
                    'operationId' => 'passkeySignInVerify',
                    'tags' => ['Auth'],
                    'x-message' => PasskeySignInVerify\Command::class,
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['response'],
                                    'properties' => [
                                        'response' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['$ref' => '#/components/responses/TokenPairSuccess'],
                        '500' => ['$ref' => '#/components/responses/InternalError'],
                    ],
                ],
            ],
        ],
    ],
];
