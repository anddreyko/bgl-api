<?php

declare(strict_types=1);

namespace App\Core\Http\Actions;

use App\Core\Http\Entities\Response;
use App\Core\Http\Helpers\HttpHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @OpenApi\Annotations\Info(
 *     title="BoarGameLog API",
 *     version="1"
 * )
 * @see \Tests\Unit\Core\Http\Actions\BaseActionTest
 */
abstract class BaseAction implements RequestHandlerInterface
{
    public function __construct(private readonly ResponseFactoryInterface $factory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return HttpHelper::json($this->factory->createResponse(), $this->content());
    }

    abstract public function content(): Response;
}
