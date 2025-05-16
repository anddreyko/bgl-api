<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Renderers;

use App\Infrastructure\Http\Renderers\JsonErrorRenderer;
use App\Infrastructure\Validation\ValidationException;
use Codeception\Test\Unit;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;

/**
 * @covers \App\Infrastructure\Http\Renderers\JsonErrorRenderer
 */
final class JsonErrorRendererTest extends Unit
{
    public function testSimple(): void
    {
        $render = new JsonErrorRenderer();

        $json = $render->__invoke(new \Exception(), false);

        $this->assertStringContainsString('Unexpected error', $json);
    }

    public function testDisplayError(): void
    {
        $render = new JsonErrorRenderer();

        $json = $render->__invoke(new \Exception(), true);

        $this->assertStringContainsString('Unexpected error', $json);
    }

    public function testHttpException(): void
    {
        $render = new JsonErrorRenderer();

        $request = $this->makeEmpty(ServerRequestInterface::class);
        $exception = new HttpException($request);
        $exception->setTitle('Http error');
        $json = $render->__invoke($exception, true);

        $this->assertStringContainsString('Http error', $json);
    }

    public function testValidationException(): void
    {
        $render = new JsonErrorRenderer();

        $validationException = new ValidationException(['token' => 'Incorrect token', 'email' => 'Incorrect email']);

        $request = $this->makeEmpty(ServerRequestInterface::class);
        $exception = new HttpException($request, 'Some error', 1, $validationException);
        $exception->setTitle('Http error');
        $json = $render->__invoke($exception, true);

        $this->assertStringContainsString('Http error', $json);
        $this->assertStringContainsString('Some error', $json);
        $this->assertStringContainsString('Incorrect token', $json);
        $this->assertStringContainsString('Incorrect email', $json);
    }
}
