<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Persistence;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryFilter;
use PhpBench\Attributes as Bench;

final class InMemoryFilterBench
{
    private InMemoryFilter $filter;

    /** @var list<array{id: string, name: string, status: string}> */
    private array $entities = [];

    public function setUp(): void
    {
        $this->filter = new InMemoryFilter();
        $this->entities = [];
    }

    /**
     * @param array{size: int} $params
     */
    private function seedEntities(int $size): void
    {
        $this->entities = [];
        for ($i = 0; $i < $size; ++$i) {
            $this->entities[] = [
                'id' => "entity-{$i}",
                'name' => "Entity Name {$i}",
                'status' => $i % 2 === 0 ? 'active' : 'inactive',
            ];
        }
    }

    /**
     * @param array{size: int} $params
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\BeforeMethods('setUp')]
    #[Bench\ParamProviders('provideCollectionSizes')]
    public function benchEquals(array $params): void
    {
        $this->seedEntities($params['size']);
        $filterFn = $this->filter->equals(new Equals(new Field('status'), 'active'));

        foreach ($this->entities as $entity) {
            $filterFn($entity);
        }
    }

    /**
     * @param array{size: int} $params
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\BeforeMethods('setUp')]
    #[Bench\ParamProviders('provideCollectionSizes')]
    public function benchContains(array $params): void
    {
        $this->seedEntities($params['size']);
        $filterFn = $this->filter->contains(new Contains(new Field('name'), 'Name 5'));

        foreach ($this->entities as $entity) {
            $filterFn($entity);
        }
    }

    /**
     * @param array{size: int} $params
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\BeforeMethods('setUp')]
    #[Bench\ParamProviders('provideCollectionSizes')]
    public function benchNestedAndOr(array $params): void
    {
        $this->seedEntities($params['size']);

        $innerOr = new OrX([
            new Equals(new Field('status'), 'active'),
            new Contains(new Field('name'), 'Entity'),
        ]);
        $outerAnd = new AndX([
            $innerOr,
            new Equals(new Field('status'), 'active'),
        ]);
        $filterFn = $outerAnd->accept($this->filter);

        foreach ($this->entities as $entity) {
            $filterFn($entity);
        }
    }

    /**
     * @return \Generator<string, array{size: int}>
     */
    public function provideCollectionSizes(): \Generator
    {
        yield '100 entities' => ['size' => 100];
        yield '500 entities' => ['size' => 500];
        yield '1000 entities' => ['size' => 1000];
    }
}
