<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Http;

use Bgl\Infrastructure\Http\LeagueRequestValidator;
use Bgl\Tests\Support\UnitTester;
use cebe\openapi\spec\OpenApi;
use Codeception\Attribute\Group;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\ServerRequestValidator as LeagueServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Bgl\Infrastructure\Http\LeagueRequestValidator
 */
#[Group('infrastructure', 'request-validator')]
final class LeagueRequestValidatorCest
{
    private LeagueRequestValidator $validator;

    public function _before(): void
    {
        $spec = new OpenApi([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Test API', 'version' => '1.0.0'],
            'paths' => [
                '/ping' => [
                    'get' => [
                        'summary' => 'Health check',
                        'responses' => [
                            '200' => ['description' => 'Success'],
                        ],
                    ],
                ],
                '/v1/users' => [
                    'post' => [
                        'summary' => 'Create user',
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
                            '200' => ['description' => 'Success'],
                        ],
                    ],
                ],
                '/v1/users/{id}' => [
                    'get' => [
                        'summary' => 'Get user',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Success'],
                        ],
                    ],
                ],
            ],
        ]);

        /** @var LeagueServerRequestValidator $leagueValidator */
        $leagueValidator = new ValidatorBuilder()->fromSchema($spec)->getServerRequestValidator();
        $this->validator = new LeagueRequestValidator($leagueValidator);
    }

    public function testValidRequestPassesThrough(UnitTester $i): void
    {
        $request = self::jsonRequest('POST', '/v1/users', [
            'email' => 'user@example.com',
            'password' => 'secret123456',
        ]);

        $errors = $this->validator->validate($request);

        $i->assertTrue($errors->isEmpty());
    }

    public function testMissingRequiredFieldReturnsError(UnitTester $i): void
    {
        $request = self::jsonRequest('POST', '/v1/users', [
            'email' => 'user@example.com',
        ]);

        $errors = $this->validator->validate($request);

        $i->assertFalse($errors->isEmpty());
    }

    public function testInvalidEmailFormatReturnsError(UnitTester $i): void
    {
        $request = self::jsonRequest('POST', '/v1/users', [
            'email' => 'not-an-email',
            'password' => 'secret123456',
        ]);

        $errors = $this->validator->validate($request);

        $i->assertFalse($errors->isEmpty());
    }

    public function testMinLengthValidation(UnitTester $i): void
    {
        $request = self::jsonRequest('POST', '/v1/users', [
            'email' => 'user@example.com',
            'password' => 'short',
        ]);

        $errors = $this->validator->validate($request);

        $i->assertFalse($errors->isEmpty());
    }

    public function testOperationWithoutRequestBodyPassesThrough(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/ping');

        $errors = $this->validator->validate($request);

        $i->assertTrue($errors->isEmpty());
    }

    public function testUnknownPathSkipsValidation(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/unknown/path');

        $errors = $this->validator->validate($request);

        $i->assertTrue($errors->isEmpty());
    }

    public function testPathWithParameterPassesThrough(UnitTester $i): void
    {
        $request = new ServerRequest('GET', '/v1/users/some-id');

        $errors = $this->validator->validate($request);

        $i->assertTrue($errors->isEmpty());
    }

    public function testTypeValidationForString(UnitTester $i): void
    {
        $request = self::jsonRequest('POST', '/v1/users', [
            'email' => 12345,
            'password' => 'secret123456',
        ]);

        $errors = $this->validator->validate($request);

        $i->assertFalse($errors->isEmpty());
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function jsonRequest(string $method, string $uri, array $body): ServerRequestInterface
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR);
        $request = new ServerRequest($method, $uri, ['Content-Type' => 'application/json']);

        return $request->withBody(Utils::streamFor($json));
    }
}
