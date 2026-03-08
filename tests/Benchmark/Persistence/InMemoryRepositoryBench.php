<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Persistence;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Page\SortFields;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Plays;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class InMemoryRepositoryBench
{
    private Plays $plays;

    /** @var list<string> */
    private array $playIds = [];

    public function setUp(): void
    {
        $this->plays = BenchHelper::get(Plays::class);
        $this->playIds = [];
        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchAdd(): void
    {
        for ($i = 0; $i < 1000; ++$i) {
            $this->plays->add(Play::create(
                id: new Uuid(\Ramsey\Uuid\Uuid::uuid4()->toString()),
                userId: new Uuid('00000000-0000-4000-8000-000000000050'),
                name: "Play {$i}",
                startedAt: new DateTime('2024-06-15 20:00:00'),
                players: new InMemoryPlayers(),
            ));
        }

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchFind(): void
    {
        $this->seedPlays(1000);

        $this->plays->find($this->playIds[500]);

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchSearch(): void
    {
        $this->seedPlays(1000);

        $filter = new Equals(new Field('userId'), '00000000-0000-4000-8000-000000000050');
        $sort = new PageSort(new SortFields(['startedAt' => SortDirection::Desc]));

        /** @var iterable<mixed> $results */
        $results = $this->plays->search($filter, new PageSize(20), new PageNumber(1), $sort);
        // Force iteration
        foreach ($results as $_) {
        }

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchCount(): void
    {
        $this->seedPlays(1000);

        $filter = new Contains(new Field('name'), 'Play 5');
        $this->plays->count($filter);

        BenchHelper::clearRepositories();
    }

    private function seedPlays(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $this->playIds[] = $id;
            $this->plays->add(Play::create(
                id: new Uuid($id),
                userId: new Uuid('00000000-0000-4000-8000-000000000050'),
                name: "Play {$i}",
                startedAt: new DateTime('2024-06-15 20:00:00'),
                players: new InMemoryPlayers(),
            ));
        }
    }
}
