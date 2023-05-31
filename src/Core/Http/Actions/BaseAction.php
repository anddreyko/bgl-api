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
    private ?ServerRequestInterface $request = null;

    public function __construct(private readonly ResponseFactoryInterface $factory)
    {
    }

    abstract public function content(): Response;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return HttpHelper::json($this->factory->createResponse(), $this->content());
    }

    public function getParam(string $name): mixed
    {
        if (!$this->request) {
            throw new \ErrorException('Request is not initialed.');
        }

        return $this->request->getQueryParams()[$name] ?? '';
    }
}
