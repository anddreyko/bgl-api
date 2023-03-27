<?php

declare(strict_types=1);

namespace App\Http\Actions\V1;

use App\Http\Entities\Response;
use App\Http\HttpHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @OA\Info(
 *     title="BoarGameLog API",
 *     version="1"
 * )
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
