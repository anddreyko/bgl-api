<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Application\Middleware\ExceptionMiddleware;
use App\Core\Exceptions\NotFoundException;
use App\Infrastructure\Http\Enums\HttpCodesEnum;
use App\Infrastructure\Localization\Translator;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ServerRequestFactory;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * @covers \App\Application\Middleware\ExceptionMiddleware
 */
final class ExceptionMiddlewareTest extends Unit
{
    private ?ExceptionMiddleware $middleware = null;
    private SymfonyTranslator $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = new SymfonyTranslator('en');
        $this->translator->addLoader('array', new ArrayLoader());
        $this->translator->addResource('array', ['Some exception.' => 'Какое-то исключение.'], 'ru', 'exceptions');

        $this->middleware = new ExceptionMiddleware(
            $this->makeEmpty(LoggerInterface::class, ['warning' => Expected::once()]),
            new Translator($this->translator)
        );
    }

    public function testHttpException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new HttpException($request, 'Some exception.', HttpCodesEnum::BadRequest->value));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::BadRequest->value);
        $this->middleware->process($request, $handler);
    }

    public function testLocalizationException(): void
    {
        $this->translator->setLocale('ru');

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \Exception('Some exception.'));

        $this->expectExceptionMessage('Какое-то исключение.');
        $this->middleware->process($request, $handler);
    }

    public function testNotFoundException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new NotFoundException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::NotFound->value);
        $this->middleware->process($request, $handler);
    }

    public function testDomainException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \DomainException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $this->middleware->process($request, $handler);
    }

    public function testLogicException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \LogicException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $this->middleware->process($request, $handler);
    }

    public function testException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \Exception('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::InternalServerError->value);
        $this->middleware->process($request, $handler);
    }

    public function testRuntimeException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \RuntimeException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::Conflict->value);
        $this->middleware->process($request, $handler);
    }

    public function testInvalidArgumentException(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test');

        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new \InvalidArgumentException('Some exception.'));

        $this->expectExceptionMessage('Some exception.');
        $this->expectExceptionCode(HttpCodesEnum::BadRequest->value);
        $this->middleware->process($request, $handler);
    }
}
