<?php

declare(strict_types=1);

namespace Bgl\Tests\Cli;

use Bgl\Tests\Support\CliTester;
use Codeception\Attribute\Group;

#[Group('console', 'openapi-export')]
final class OpenApiExportCest
{
    private const string OUTPUT_PATH = 'web/openapi.json';

    public function testCommandExecutesSuccessfully(CliTester $i): void
    {
        $this->cleanup();

        $i->runShellCommand('php cli/app openapi:export');
        $i->seeResultCodeIs(0);
        $i->seeInShellOutput('OpenAPI spec exported to');
    }

    public function testOutputFileIsValidJson(CliTester $i): void
    {
        $this->cleanup();

        $i->runShellCommand('php cli/app openapi:export');

        $i->assertFileExists(self::OUTPUT_PATH);

        $content = (string) file_get_contents(self::OUTPUT_PATH);
        $decoded = json_decode($content, true);

        $i->assertNotNull($decoded, 'Output file must contain valid JSON');
        $i->assertIsArray($decoded);
    }

    public function testOutputContainsExpectedPaths(CliTester $i): void
    {
        $this->cleanup();

        $i->runShellCommand('php cli/app openapi:export');

        $content = (string) file_get_contents(self::OUTPUT_PATH);

        /** @var array<string, mixed> $spec */
        $spec = json_decode($content, true);

        $i->assertArrayHasKey('paths', $spec);
        $i->assertArrayHasKey('/ping', $spec['paths']);
        $i->assertArrayHasKey('/v1/auth/password/sign-up', $spec['paths']);
    }

    public function testOutputDoesNotContainInternalExtensions(CliTester $i): void
    {
        $this->cleanup();

        $i->runShellCommand('php cli/app openapi:export');

        $content = (string) file_get_contents(self::OUTPUT_PATH);

        $i->assertStringNotContainsString('"x-message"', $content);
        $i->assertStringNotContainsString('"x-interceptors"', $content);
        $i->assertStringNotContainsString('"x-auth"', $content);
        $i->assertStringNotContainsString('"x-map"', $content);
    }

    private function cleanup(): void
    {
        if (file_exists(self::OUTPUT_PATH)) {
            unlink(self::OUTPUT_PATH);
        }
    }
}
