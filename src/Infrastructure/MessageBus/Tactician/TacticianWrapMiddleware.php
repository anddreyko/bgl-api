<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\MessageBus\Tactician;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageMiddleware;
use League\Tactician\Middleware;
use Psr\Container\ContainerInterface;

final readonly class TacticianWrapMiddleware implements Middleware
{
    /**
     * @param class-string<MessageMiddleware> $middleware
     */
    public function __construct(private string $middleware, private ContainerInterface $container)
    {
    }

    #[\Override]
    public function execute($command, callable $next): mixed
    {
        if ($command instanceof Envelope) {
            /** @var MessageMiddleware $middleware */
            $middleware = $this->container->get($this->middleware);

            return $middleware($command, new NextHandler($next(...)));
        }

        /** @var Middleware $middleware */
        $middleware = $this->container->get($this->middleware);

        return $middleware->execute($command, $next(...));
    }
}
