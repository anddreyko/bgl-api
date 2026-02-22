<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Api;

use Bgl\Presentation\Api\ApiAction;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @covers \Bgl\Presentation\Api\ApiAction
 */
#[Group('presentation', 'api-action')]
final class ApiActionCest
{
    private ApiAction $action;

    public function _before(): void
    {
        /** @var \DI\Container $container */
        $container = require __DIR__ . '/../../../config/container.php';
        /** @var ApiAction $action */
        $action = $container->get(ApiAction::class);
        $this->action = $action;
    }

    public function testPingEndpoint(FunctionalTester $i): void
    {
        $request = new ServerRequestFactory()->createServerRequest('GET', '/ping');

        $response = $this->action->handle($request);

        $i->assertSame(200, $response->getStatusCode());
        $i->assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        /** @var array{code: int, data: array<string, mixed>} $data */
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $i->assertSame(0, $data['code']);
        $i->assertArrayHasKey('data', $data);
    }

    public function testNotFoundRoute(FunctionalTester $i): void
    {
        $request = new ServerRequestFactory()->createServerRequest('GET', '/nonexistent');

        $response = $this->action->handle($request);

        $i->assertSame(404, $response->getStatusCode());

        $body = (string) $response->getBody();
        /** @var array{code: int, message: string} $data */
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $i->assertSame(1, $data['code']);
        $i->assertSame('Not Found', $data['message']);
    }

    public function testMethodNotAllowed(FunctionalTester $i): void
    {
        $request = new ServerRequestFactory()->createServerRequest('POST', '/ping');

        $response = $this->action->handle($request);

        $i->assertSame(404, $response->getStatusCode());
    }
}
