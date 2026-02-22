<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Presentation\Api\Interceptors\Interceptor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class InterceptorPipeline
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * @param list<class-string<Interceptor>> $interceptorClasses
     */
    public function process(ServerRequestInterface $request, array $interceptorClasses): ServerRequestInterface
    {
        foreach ($interceptorClasses as $interceptorClass) {
            /** @var Interceptor $interceptor */
            $interceptor = $this->container->get($interceptorClass);
            $request = $interceptor->process($request);
        }

        return $request;
    }
}
