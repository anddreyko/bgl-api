<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Http;

use Bgl\Infrastructure\Http\OpenApiRequestValidator;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \Bgl\Infrastructure\Http\OpenApiRequestValidator
 */
#[Group('infrastructure', 'request-validator')]
final class OpenApiRequestValidatorCest
{
    private OpenApiRequestValidator $validator;

    public function _before(): void
    {
        $this->validator = new OpenApiRequestValidator();
    }

    public function testValidRequestPassesThrough(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'required' => ['email', 'password'],
                            'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 6],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }

    public function testMissingRequiredFieldReturnsError(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => 'user@example.com',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'required' => ['email', 'password'],
                            'properties' => [
                                'email' => ['type' => 'string'],
                                'password' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('password', $errors);
        $i->assertSame(['The password field is required'], $errors['password']);
    }

    public function testEmptyRequiredFieldReturnsError(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => '',
            'password' => 'secret123',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'required' => ['email', 'password'],
                            'properties' => [
                                'email' => ['type' => 'string'],
                                'password' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
        $i->assertSame(['The email field is required'], $errors['email']);
    }

    public function testInvalidEmailFormatReturnsError(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => 'not-an-email',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
        $i->assertContains('The email field must be a valid email', $errors['email']);
    }

    public function testMinLengthValidation(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'password' => 'short',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'password' => ['type' => 'string', 'minLength' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('password', $errors);
        $i->assertContains('The password field must be at least 8 characters', $errors['password']);
    }

    public function testMaxLengthValidation(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'name' => str_repeat('a', 256),
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'name' => ['type' => 'string', 'maxLength' => 255],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('name', $errors);
        $i->assertContains('The name field must be at most 255 characters', $errors['name']);
    }

    public function testTypeValidationForString(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => 12345,
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'email' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
        $i->assertContains('The email field must be of type string', $errors['email']);
    }

    public function testOperationWithoutRequestBodyPassesThrough(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/ping');

        $operation = [
            'summary' => 'Health check',
            'x-message' => 'SomeCommand',
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }

    public function testRequiredQueryParameterValidation(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users');

        $operation = [
            'parameters' => [
                [
                    'name' => 'page',
                    'in' => 'query',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('page', $errors);
        $i->assertContains('The page parameter is required', $errors['page']);
    }

    public function testOptionalQueryParameterIsNotRequired(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users');

        $operation = [
            'parameters' => [
                [
                    'name' => 'page',
                    'in' => 'query',
                    'required' => false,
                    'schema' => ['type' => 'integer'],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }

    public function testEnumValidation(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'role' => 'superadmin',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'role' => ['type' => 'string', 'enum' => ['admin', 'user', 'moderator']],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('role', $errors);
        $i->assertContains('The role field must be one of: admin, user, moderator', $errors['role']);
    }

    public function testValidEnumValuePasses(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'role' => 'admin',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'role' => ['type' => 'string', 'enum' => ['admin', 'user', 'moderator']],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }

    public function testUuidFormatValidation(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'id' => 'not-a-uuid',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'id' => ['type' => 'string', 'format' => 'uuid'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('id', $errors);
        $i->assertContains('The id field must be a valid uuid', $errors['id']);
    }

    public function testValidUuidPasses(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'id' => ['type' => 'string', 'format' => 'uuid'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }

    public function testMultipleErrorsOnSameField(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'email' => 'ab',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email', 'minLength' => 5],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
        $i->assertGreaterThanOrEqual(2, count($errors['email']));
    }

    public function testMultipleFieldErrors(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'required' => ['email', 'password'],
                            'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
        $i->assertArrayHasKey('password', $errors);
    }

    public function testPathParameterValidation(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users/abc');

        $operation = [
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string', 'format' => 'uuid'],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation, ['id' => 'abc']);

        $i->assertArrayHasKey('id', $errors);
        $i->assertContains('The id field must be a valid uuid', $errors['id']);
    }

    public function testMinimumNumberValidation(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users');
        $request = $request->withQueryParams(['page' => '0']);

        $operation = [
            'parameters' => [
                [
                    'name' => 'page',
                    'in' => 'query',
                    'schema' => ['type' => 'integer', 'minimum' => 1],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('page', $errors);
        $i->assertContains('The page field must be at least 1', $errors['page']);
    }

    public function testNullBodyWithRequiredFieldsReturnsErrors(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/users', ['Content-Type' => 'application/json']);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'required' => ['email'],
                            'properties' => [
                                'email' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('email', $errors);
    }

    public function testDateFormatValidation(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/events', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'date' => 'not-a-date',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'date' => ['type' => 'string', 'format' => 'date'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertArrayHasKey('date', $errors);
        $i->assertContains('The date field must be a valid date', $errors['date']);
    }

    public function testValidDatePasses(UnitTester $i): void
    {
        $request = new ServerRequest('POST', '/v1/events', ['Content-Type' => 'application/json']);
        $request = $request->withParsedBody([
            'date' => '2025-06-15',
        ]);

        $operation = [
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'properties' => [
                                'date' => ['type' => 'string', 'format' => 'date'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $errors = $this->validator->validate($request, $operation);

        $i->assertSame([], $errors);
    }
}
