<?php

declare(strict_types=1);

namespace App\Core\Http\Actions;

use App\Core\Http\Entities\Response;
use App\Core\Http\Helpers\HttpHelper;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @OpenApi\Annotations\Info(
 *     title="BoarGameLog API",
 *     version="1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     in="header",
 *     name="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * ),
 * @see \Tests\Unit\Core\Http\Actions\BaseActionTest
 */
abstract class BaseAction
{
    public ?ServerRequestInterface $request = null;
    /** @var mixed[] */
    private array $args = [];

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed[] $args
     *
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->request = $request;
        $this->args = $args;

        return HttpHelper::json($response, $this->content());
    }

    abstract public function content(): Response;

    public function getParam(string $name): mixed
    {
        return $this->request?->getQueryParams()[$name] ?? $this->request?->getParsedBody()[$name] ?? '';
    }

    public function getArgs(string $name): string
    {
        return (string)($this->args[$name] ?? null);
    }

    public function getAttribute(string $name): mixed
    {
        return $this->request?->getAttribute($name);
    }
}
