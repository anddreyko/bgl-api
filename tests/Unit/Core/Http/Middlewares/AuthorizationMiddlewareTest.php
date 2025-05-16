<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Application\Middleware\AuthorizationMiddleware;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Auth\Services\AuthorizationService;
use App\Infrastructure\Tokens\JsonWebTokenizer;
use Codeception\Test\Unit;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

/**
 * @covers \App\Application\Middleware\AuthorizationMiddleware
 */
final class AuthorizationMiddlewareTest extends Unit
{
    public function testSuccessAuth(): void
    {
        $auth = new JsonWebTokenizer(new Key('some-key', env('JWT_ALGO')));
        $token = $auth->encode(payload: ['user' => Uuid::NIL]);
        $user = User::createByEmail(
            id: new Id(Uuid::NIL),
            email: new Email('auth@app.test'),
            hash: new PasswordHash(Uuid::NIL),
            createdAt: new \DateTimeImmutable()
        );
        $user->setTokenAccess($token);
        $authorizationService = new AuthorizationService(
            $auth,
            $this->makeEmpty(
                UserRepository::class,
                [
                    'getById' => $user,
                    'hasTokenAccess' => fn(User $user, WebToken $token) => current($user->getTokenAccess())
                            ->getValue() === $token->getValue(),
                ]
            )
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(RouteContext::ROUTE, $this->makeEmpty(RouteInterface::class))
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class))
            ->withHeader('Authorization', "Bearer {$token->getValue()}");

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) use ($user) {
                    self::assertEquals($user, $request->getAttribute(AuthorizationMiddleware::ATTRIBUTE_IDENTITY));

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }

    public function testAllowWithoutAuth(): void
    {
        $authorizationService = new AuthorizationService(
            new JsonWebTokenizer($this->make(Key::class)),
            $this->makeEmpty(UserRepository::class)
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(
                RouteContext::ROUTE,
                $this->makeEmpty(
                    RouteInterface::class,
                    ['getArgument' => fn(string $arg) => [AuthorizationMiddleware::ATTRIBUTE_ACCESSED => '1'][$arg]]
                )
            )
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class));

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) {
                    self::assertEquals(null, $request->getAttribute(AuthorizationMiddleware::ATTRIBUTE_IDENTITY));

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }

    public function testUnauthorizedException(): void
    {
        $authorizationService = new AuthorizationService(
            new JsonWebTokenizer($this->make(Key::class)),
            $this->makeEmpty(UserRepository::class)
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(RouteContext::ROUTE, $this->makeEmpty(RouteInterface::class))
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class));

        $handler = $this->makeEmpty(RequestHandlerInterface::class);

        $this->expectException(HttpUnauthorizedException::class);
        $middleware->process($request, $handler);
    }

    public function testEmptyBearer(): void
    {
        $authorizationService = new AuthorizationService(
            new JsonWebTokenizer($this->make(Key::class)),
            $this->makeEmpty(UserRepository::class)
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(RouteContext::ROUTE, $this->makeEmpty(RouteInterface::class))
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class))
            ->withHeader('Authorization', "Bearer ");

        $handler = $this->makeEmpty(RequestHandlerInterface::class);

        $this->expectException(HttpUnauthorizedException::class);
        $middleware->process($request, $handler);
    }

    public function testIncorrectBearer(): void
    {
        $authorizationService = new AuthorizationService(
            new JsonWebTokenizer($this->make(Key::class)),
            $this->makeEmpty(UserRepository::class)
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(RouteContext::ROUTE, $this->makeEmpty(RouteInterface::class))
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class))
            ->withHeader('Authorization', "Bearer incorrect");

        $handler = $this->makeEmpty(RequestHandlerInterface::class);

        $this->expectException(\UnexpectedValueException::class);
        $middleware->process($request, $handler);
    }
}
