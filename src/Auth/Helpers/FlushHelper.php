<?php

declare(strict_types=1);

namespace App\Auth\Helpers;

use Doctrine\ORM\EntityManagerInterface;

final readonly class FlushHelper
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}
