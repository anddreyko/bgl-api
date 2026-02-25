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
                ],
            ],
            '/v1/auth/sign-in' => [
                'post' => [
                    'summary' => 'Login with credentials',
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
                ],
            ],
            '/v1/auth/confirm/{token}' => [
                'get' => [
                    'summary' => 'Confirm email',
                    'x-message' => ConfirmEmail\Command::class,
                    'parameters' => [
                        [
                            'name' => 'token',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            '/v1/auth/refresh' => [
                'post' => [
                    'summary' => 'Refresh token pair',
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
                ],
            ],
            '/v1/auth/sign-out' => [
                'post' => [
                    'summary' => 'Sign out',
                    'x-message' => SignOut\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                ],
            ],
            '/v1/auth/passkey/register' => [
                'post' => [
                    'summary' => 'Get passkey registration options',
                    'x-message' => RegisterPasskeyOptions\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
                ],
            ],
            '/v1/auth/passkey/register/verify' => [
                'post' => [
                    'summary' => 'Verify passkey registration',
                    'x-message' => RegisterPasskeyVerify\Command::class,
                    'x-interceptors' => [AuthInterceptor::class],
                    'x-auth' => ['userId'],
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
                ],
            ],
            '/v1/auth/passkey/sign-in' => [
                'post' => [
                    'summary' => 'Get passkey sign-in options',
                    'x-message' => PasskeySignInOptions\Command::class,
                ],
            ],
            '/v1/auth/passkey/sign-in/verify' => [
                'post' => [
                    'summary' => 'Verify passkey sign-in',
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
                ],
            ],
        ],
    ],
];
