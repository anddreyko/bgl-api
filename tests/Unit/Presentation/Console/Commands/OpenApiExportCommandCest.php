<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Presentation\Console\Commands;

use Bgl\Presentation\Console\Commands\OpenApiExportCommand;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Presentation\Console\Commands\OpenApiExportCommand
 */
#[Group('presentation', 'console', 'openapi-export')]
final class OpenApiExportCommandCest
{
    public function testStripsInternalExtensions(UnitTester $i): void
    {
        $data = [
            'summary' => 'Test endpoint',
            'x-message' => 'SomeCommand',
            'x-interceptors' => ['AuthInterceptor'],
            'x-auth' => ['userId'],
            'x-map' => ['id' => 'entityId'],
            'requestBody' => [
                'required' => true,
            ],
        ];

        $result = OpenApiExportCommand::stripInternalKeys($data);

        $i->assertArrayHasKey('summary', $result);
        $i->assertArrayHasKey('requestBody', $result);
        $i->assertArrayNotHasKey('x-message', $result);
        $i->assertArrayNotHasKey('x-interceptors', $result);
        $i->assertArrayNotHasKey('x-auth', $result);
        $i->assertArrayNotHasKey('x-map', $result);
    }

    public function testPreservesStandardOpenApiFields(UnitTester $i): void
    {
        $data = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'BoardGameLog API',
                'version' => '1.0.0',
            ],
            'paths' => [
                '/ping' => [
                    'get' => [
                        'summary' => 'Health check',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path'],
                        ],
                    ],
                ],
            ],
        ];

        $result = OpenApiExportCommand::stripInternalKeys($data);

        $i->assertSame('3.0.0', $result['openapi']);
        $i->assertSame('BoardGameLog API', $result['info']['title']);
        $i->assertSame('Health check', $result['paths']['/ping']['get']['summary']);
        $i->assertSame('id', $result['paths']['/ping']['get']['parameters'][0]['name']);
    }

    public function testStripsNestedInternalKeys(UnitTester $i): void
    {
        $data = [
            'paths' => [
                '/v1/auth/sign-out' => [
                    'post' => [
                        'summary' => 'Sign out',
                        'x-message' => 'SignOut\\Command',
                        'x-interceptors' => ['AuthInterceptor'],
                        'x-auth' => ['userId'],
                    ],
                ],
                '/v1/plays/sessions/{id}' => [
                    'patch' => [
                        'summary' => 'Close play session',
                        'x-message' => 'CloseSession\\Command',
                        'x-map' => ['id' => 'sessionId'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path'],
                        ],
                    ],
                ],
            ],
        ];

        $result = OpenApiExportCommand::stripInternalKeys($data);

        $signOut = $result['paths']['/v1/auth/sign-out']['post'];
        $i->assertSame('Sign out', $signOut['summary']);
        $i->assertArrayNotHasKey('x-message', $signOut);
        $i->assertArrayNotHasKey('x-interceptors', $signOut);
        $i->assertArrayNotHasKey('x-auth', $signOut);

        $closeSession = $result['paths']['/v1/plays/sessions/{id}']['patch'];
        $i->assertSame('Close play session', $closeSession['summary']);
        $i->assertArrayNotHasKey('x-message', $closeSession);
        $i->assertArrayNotHasKey('x-map', $closeSession);
        $i->assertArrayHasKey('parameters', $closeSession);
    }

    public function testReturnsEmptyArrayForEmptyInput(UnitTester $i): void
    {
        $result = OpenApiExportCommand::stripInternalKeys([]);

        $i->assertSame([], $result);
    }

    public function testPreservesNonInternalExtensions(UnitTester $i): void
    {
        $data = [
            'x-custom' => 'keep this',
            'x-message' => 'strip this',
        ];

        $result = OpenApiExportCommand::stripInternalKeys($data);

        $i->assertArrayHasKey('x-custom', $result);
        $i->assertArrayNotHasKey('x-message', $result);
    }
}
