<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Auth\Entities\User;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Http\Middlewares\AuthorizationMiddleware;
use App\Core\Http\Services\AuthorizationService;
use App\Core\Tokens\Services\JsonWebTokenizerService;
use Codeception\Test\Unit;
use Firebase\JWT\JWT;
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
 * @covers \App\Core\Http\Middlewares\AuthorizationMiddleware
 */
final class AuthorizationMiddlewareTest extends Unit
{
    public function testSuccessAuth(): void
    {
        $auth = new JsonWebTokenizerService($this->make(JWT::class), new Key('some-key', 'HS512'));
        $token = $auth->encode(payload: ['user' => Uuid::NIL], issuedAt: new \DateTimeImmutable())->getValue();
        $user = User::createByEmail(
            id: new Id(Uuid::NIL),
            email: new Email('auth@app.test'),
            hash: new PasswordHash(Uuid::NIL),
            token: new Token(Uuid::NIL, new \DateTimeImmutable()),
            createdAt: new \DateTimeImmutable()
        );
        $authorizationService = new AuthorizationService(
            $auth,
            $this->makeEmpty(UserRepository::class, ['getById' => $user])
        );

        $middleware = new AuthorizationMiddleware($authorizationService);

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withAttribute(RouteContext::ROUTE, $this->makeEmpty(RouteInterface::class))
            ->withAttribute(RouteContext::ROUTE_PARSER, $this->makeEmpty(RouteParserInterface::class))
            ->withAttribute(RouteContext::ROUTING_RESULTS, $this->makeEmpty(RoutingResults::class))
            ->withHeader('Authorization', "Bearer $token");

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
            new JsonWebTokenizerService($this->make(JWT::class), $this->make(Key::class)),
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
            new JsonWebTokenizerService($this->make(JWT::class), $this->make(Key::class)),
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
            new JsonWebTokenizerService($this->make(JWT::class), $this->make(Key::class)),
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
            new JsonWebTokenizerService($this->make(JWT::class), $this->make(Key::class)),
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
