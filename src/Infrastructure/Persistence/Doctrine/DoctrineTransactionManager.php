<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Persistence\TransactionManager;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionManager implements TransactionManager
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[\Override]
    public function beginTransaction(): void
    {
        $this->em->beginTransaction();
    }

    #[\Override]
    public function flush(): void
    {
        $this->em->flush();
    }

    #[\Override]
    public function commit(): void
    {
        $this->em->commit();
    }

    #[\Override]
    public function rollback(): void
    {
        $this->em->rollback();
    }
}
