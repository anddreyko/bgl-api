<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\MessageBus\Tactician;

use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\EnvelopeFactory;
use Bgl\Core\Messages\Message;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Messages\MessageIdGenerator;
use Bgl\Core\Messages\MessageMiddleware;
use DI\Container;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\Locator\CallableLocator;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use Psr\Container\ContainerInterface;

/**
 * @see \Bgl\Tests\Integration\MessageBus\TacticianDispatcherCest
 */
final readonly class TacticianDispatcher implements Dispatcher
{
    private CommandBus $commandBus;

    /**
     * @param list<array{0: class-string<Message>, 1: class-string<MessageHandler>, 2: MessageMiddleware[]}> $handlers
     * @param list<class-string<MessageMiddleware>> $middleware
     */
    public function __construct(
        array $handlers,
        array $middleware,
        private MessageIdGenerator $messageIdGenerator,
        private EnvelopeFactory $envelopeFactory,
        ContainerInterface $container,
    ) {
        $container = new Container(
            definitions: array_merge(
                ...array_map(
                    static fn($map) => [
                        $map[0] => static fn(ContainerInterface $container): mixed => $container->get($map[1]),
                    ],
                    $handlers
                )
            ),
            wrapperContainer: $container
        );
        $this->commandBus = new CommandBus([
            ...array_map(
                static fn($messageMiddleware) => new TacticianWrapMiddleware($messageMiddleware, $container),
                $middleware
            ),
            new CommandHandlerMiddleware(
                new TacticianCommandNameExtractor(),
                new CallableLocator($container->get(...)),
                new InvokeInflector()
            ),
        ]);
    }

    /**
     * @template TResult of mixed
     * @param Message<TResult> $message
     * @param Envelope|null $parent
     *
     * @return TResult
     */
    #[\Override]
    public function dispatch(
        Message $message,
        ?Envelope $parent = null
    ): mixed {
        $messageId = $this->messageIdGenerator->generate();

        /** @var Envelope<Message<TResult>> $envelope */
        $envelope = $this->envelopeFactory->build($message, $messageId, $parent);

        /** @var TResult */
        return $this->commandBus->handle($envelope);
    }
}
