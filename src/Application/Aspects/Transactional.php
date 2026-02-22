<?php

declare(strict_types=1);

namespace Bgl\Application\Aspects;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\Messages\MessageMiddleware;
use Bgl\Core\Persistence\TransactionManager;

/**
 * @see \Bgl\Tests\Functional\TransactionalAspectCest
 */
final readonly class Transactional implements MessageMiddleware
{
    public function __construct(private TransactionManager $transactionManager)
    {
    }

    #[\Override]
    public function __invoke(Envelope $envelope, MessageHandler $handler): mixed
    {
        $this->transactionManager->beginTransaction();

        try {
            $result = $handler($envelope);
        } catch (\Throwable $exception) {
            $this->transactionManager->rollback();

            throw $exception;
        }

        $this->transactionManager->flush();
        $this->transactionManager->commit();

        return $result;
    }
}
