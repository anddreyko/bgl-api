<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Http\Enums\HttpCodesEnum;
use App\Core\Localization\Services\TranslatorService;
use App\Core\Validation\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\ExceptionMiddlewareTest
 */
final readonly class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger, private TranslatorService $translator)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $exception) {
            $code = match (true) {
                $exception instanceof HttpException => HttpCodesEnum::from($exception->getCode()),
                $exception instanceof NotFoundException => HttpCodesEnum::NotFound,
                $exception instanceof ValidationException => HttpCodesEnum::UnprocessableEntity,
                $exception instanceof UnauthorizedException => HttpCodesEnum::Unauthorized,
                $exception instanceof \InvalidArgumentException => HttpCodesEnum::BadRequest,
                $exception instanceof \RuntimeException => HttpCodesEnum::Conflict,
                default => HttpCodesEnum::InternalServerError
            };
        }

        $this->logger->warning($exception->getMessage(), ['exception' => $exception, 'url' => $request->getUri()]);

        $exception = new HttpException(
            $request,
            $this->translator->trans(id: $exception->getMessage() ?: $code->label(), domain: 'exceptions'),
            $code->value,
            $exception
        );

        $exception->setTitle($exception->getMessage());

        throw $exception;
    }
}
