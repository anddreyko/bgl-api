<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Persistence;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Page\SortFields;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\PlayersFactory;
use Bgl\Domain\Plays\Plays;
use Bgl\Tests\Benchmark\DoctrineBenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
#[Bench\Assert('mode(variant.time.avg) <= mode(baseline.time.avg) +/- 15%')]
final class DoctrineRepositoryBench
{
    private Plays $plays;
    private PlayersFactory $playersFactory;
    private int $counter = 0;

    public function setUp(): void
    {
        DoctrineBenchHelper::createSchema();
        $this->plays = DoctrineBenchHelper::get(Plays::class);
        $this->playersFactory = DoctrineBenchHelper::get(PlayersFactory::class);
        $this->counter = 0;
        DoctrineBenchHelper::truncateAll();
        $this->seedPlays(100);
    }

    public function tearDown(): void
    {
        DoctrineBenchHelper::truncateAll();
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchAdd(): void
    {
        ++$this->counter;
        for ($i = 0; $i < 100; ++$i) {
            $this->plays->add(
                Play::create(
                    id: new Uuid("bench-add-{$this->counter}-{$i}"),
                    userId: new Uuid('bench-user'),
                    name: "Add Play {$this->counter}-{$i}",
                    startedAt: new DateTime('2024-06-15 20:00:00'),
                    players: $this->playersFactory->createEmpty(),
                )
            );
        }

        DoctrineBenchHelper::flush();
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchFind(): void
    {
        DoctrineBenchHelper::clear();
        $this->plays->find('bench-play-50');
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchSearch(): void
    {
        DoctrineBenchHelper::clear();

        $filter = new Equals(new Field('userId'), 'bench-user');
        $sort = new PageSort(new SortFields(['startedAt' => SortDirection::Desc]));

        /** @var iterable<mixed> $results */
        $results = $this->plays->search($filter, new PageSize(20), new PageNumber(1), $sort);

        foreach ($results as $_) {
        }
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchCount(): void
    {
        $filter = new Contains(new Field('name'), 'Play 5');
        $this->plays->count($filter);
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchContainsFilter(): void
    {
        DoctrineBenchHelper::clear();

        $filter = new Contains(new Field('name'), 'Play');

        /** @var iterable<mixed> $results */
        $results = $this->plays->search($filter, new PageSize(20));

        foreach ($results as $_) {
        }
    }

    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    public function benchNestedFilter(): void
    {
        DoctrineBenchHelper::clear();

        $filter = new AndX([
            new Equals(new Field('userId'), 'bench-user'),
            new OrX([
                new Contains(new Field('name'), 'Play 1'),
                new Contains(new Field('name'), 'Play 2'),
            ]),
        ]);

        /** @var iterable<mixed> $results */
        $results = $this->plays->search($filter, new PageSize(20));

        foreach ($results as $_) {
        }
    }

    private function seedPlays(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $this->plays->add(
                Play::create(
                    id: new Uuid("bench-play-{$i}"),
                    userId: new Uuid('bench-user'),
                    name: "Play {$i}",
                    startedAt: new DateTime('2024-06-15 20:00:00'),
                    players: $this->playersFactory->createEmpty(),
                )
            );
        }

        DoctrineBenchHelper::flush();
        DoctrineBenchHelper::clear();
    }
}
