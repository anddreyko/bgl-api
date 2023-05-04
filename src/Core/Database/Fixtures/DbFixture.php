<?php

declare(strict_types=1);

namespace App\Core\Database\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @see \Tests\Unit\Core\Database\Fixtures\DbFixtureTest
 */
abstract class DbFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        if ($manager instanceof EntityManagerInterface) {
            $this->fixture($manager);
        } else {
            throw new \RuntimeException('Manager should be EntityManagerInterface');
        }
    }

    abstract public function fixture(EntityManagerInterface $manager): void;
}
