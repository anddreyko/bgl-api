<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Core\Exceptions\NotFoundException;
use App\Core\Http\Enums\HttpCodesEnum;
use App\Core\Http\Middlewares\ExceptionMiddleware;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ServerRequestFactory;

/**
 * @covers \App\Core\Http\Middlewares\ExceptionMiddleware
 */
final class ExceptionMiddlewareTest extends Unit
{
    public function testHttpException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new HttpException($request, 'Some exception.', HttpCodesEnum::BadRequest->value));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::BadRequest->value);
        $middleware->process($request, $handler);
    }

    public function testNotFoundException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new NotFoundException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::NotFound->value);
        $middleware->process($request, $handler);
    }

    public function testDomainException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \DomainException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $middleware->process($request, $handler);
    }

    public function testLogicException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \LogicException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $middleware->process($request, $handler);
    }

    public function testException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \Exception('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $middleware->process($request, $handler);
    }

    public function testRuntimeException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \RuntimeException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::Conflict->value);
        $middleware->process($request, $handler);
    }

    public function testInvalidArgumentException(): void
    {
        $logger = $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]);

        $middleware = new ExceptionMiddleware($logger);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \InvalidArgumentException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::BadRequest->value);
        $middleware->process($request, $handler);
    }
}
