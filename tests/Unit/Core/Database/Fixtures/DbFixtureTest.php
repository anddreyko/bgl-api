<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Database\Fixtures;

use App\Infrastructure\Database\Fixtures\DbFixture;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @covers \App\Infrastructure\Database\Fixtures\DbFixture
 */
final class DbFixtureTest extends Unit
{
    public function testSuccessLoad(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $dbFixture = $this->make(DbFixture::class, ['fixture' => Expected::once()]);

        $dbFixture->load($em);
    }

    public function testFailedLoad(): void
    {
        $em = $this->createStub(ObjectManager::class);
        $dbFixture = $this->make(DbFixture::class, ['fixture' => Expected::never()]);

        $this->expectException(\RuntimeException::class);
        $dbFixture->load($em);
    }
}
