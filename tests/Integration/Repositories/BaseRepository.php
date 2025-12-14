<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Repositories;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Searchable;
use Bgl\Tests\Support\IntegrationTester;
use Bgl\Tests\Support\Repositories\TestEntity;
use Codeception\Example;
use Codeception\Scenario;

abstract class BaseRepository
{
    /**
     * @param list<array{0: int, 1?: string}> $entities
     */
    abstract public function getRepository(array $entities = []): Repository|Searchable;

    final public function testAdd(IntegrationTester $i): void
    {
        $i->assertEquals(null, $this->getRepository()->find('1234'));

        $entity = new TestEntity('1234', 'entity 1');
        $this->getRepository()->add($entity);

        $i->assertEquals('1234', $this->getRepository()->find('1234')->getId());
    }

    final public function testRemove(IntegrationTester $i): void
    {
        $entity = new TestEntity('1234', 'entity 1');

        $this->getRepository()->remove($entity);
        $i->assertEquals(null, $this->getRepository()->find('1234'));

        $this->getRepository()->add($entity);
        $this->getRepository()->remove($entity);

        $i->assertEquals(null, $this->getRepository()->find('1234'));
    }


    /**
     * @dataProvider providerQueryDefaultCall
     */
    final public function testQueryDefaultCall(IntegrationTester $i, Scenario $scenario, Example $data): void
    {
        $entities = $data['entities'];
        $repository = $this->getRepository($entities);

        $actual = $repository->search(filter: All::Filter);

        $i->assertEquals($entities, $actual);
    }

    private function providerQueryDefaultCall(): iterable
    {
        yield [
            'entities' => [],
        ];
        yield [
            'entities' => [new TestEntity('1', '1'), new TestEntity('2', '2'), new TestEntity('3', '3')],
        ];
        yield [
            'entities' => [new TestEntity('2', '2'), new TestEntity('4', '4')],
        ];
    }

    /**
     * @dataProvider providerFilter
     */
    final public function testFilter(IntegrationTester $i, Scenario $scenario, Example $data): void
    {
        /** @var Filter $filter */
        $filter = $data['filter'];
        $expected = $data['expected'];

        $repository = $this->getRepository([
            new TestEntity('1', 'a'),
            new TestEntity('2', 'b'),
            new TestEntity('3', 'c'),
            new TestEntity('4', 'c'),
        ]);

        $actual = $repository->search(filter: $filter);

        $i->assertEquals($expected, $actual);
    }

    private function providerFilter(): iterable
    {
        yield [
            'filter' => None::Filter,
            'expected' => [],
        ];
        yield [
            'filter' => new Equals(new Field('id'), '1'),
            'expected' => [new TestEntity('1', 'a')],
        ];
        yield [
            'filter' => new Equals('c', new Field('value')),
            'expected' => [new TestEntity('3', 'c'), new TestEntity('4', 'c')],
        ];
        yield [
            'filter' => new Greater(new Field('id'), '2'),
            'expected' => [new TestEntity('3', 'c'), new TestEntity('4', 'c')],
        ];
        yield [
            'filter' => new Less(new Field('value'), 'c'),
            'expected' => [new TestEntity('1', 'a'), new TestEntity('2', 'b')],
        ];
        yield [
            'filter' => new AndX([new Equals(new Field('id'), '1'), new Equals(new Field('value'), 'c')]),
            'expected' => [],
        ];
        yield [
            'filter' => new OrX([new Equals(new Field('id'), '1'), new Equals(new Field('value'), 'b')]),
            'expected' => [new TestEntity('1', 'a'), new TestEntity('2', 'b')],
        ];
        yield [
            'filter' => new OrX([new Equals(new Field('id'), '1'), new Equals(new Field('value'), 'b')]),
            'expected' => [new TestEntity('1', 'a'), new TestEntity('2', 'b')],
        ];
    }

    final public function testSort(IntegrationTester $i): void
    {
        $repository = $this->getRepository([
            new TestEntity('1', 'a'),
            new TestEntity('2', 'b'),
            new TestEntity('3', 'c'),
        ]);

        $entities = $repository->search(filter: All::Filter, sort: new PageSort(['id' => SortDirection::Desc]));

        $i->assertEquals([
            new TestEntity('3', 'c'),
            new TestEntity('2', 'b'),
            new TestEntity('1', 'a'),
        ], $entities);
    }

    final public function testMultiSort(IntegrationTester $i): void
    {
        $entity1B = new TestEntity('1', 'b');
        $entity2C = new TestEntity('2', 'c');
        $entity3B = new TestEntity('3', 'b');
        $repository = $this->getRepository([$entity3B, $entity2C, $entity1B]);

        $entities = $repository->search(
            filter: All::Filter,
            sort: new PageSort(['value' => SortDirection::Desc, 'id' => SortDirection::Asc])
        );

        $i->assertEquals([$entity2C, $entity1B, $entity3B], $entities);
    }

    /**
     * @dataProvider providerOffsetLimit
     */
    final public function testOffsetLimit(IntegrationTester $i, Scenario $scenario, Example $data): void
    {
        $offset = $data['offset'];
        $limit = $data['limit'];
        $expected = $data['expected'];
        $repository = $this->getRepository([
            new TestEntity('1', 'a'),
            new TestEntity('2', 'b'),
            new TestEntity('3', 'c'),
        ]);

        $entities = $repository->search(
            filter: All::Filter,
            size: new PageSize($limit),
            number: new PageNumber($offset)
        );

        $i->assertEquals($expected, $entities);
    }

    private function providerOffsetLimit(): iterable
    {
        yield [
            'offset' => 1,
            'limit' => 1,
            'expected' => [new TestEntity('1', 'a')],
        ];
        yield [
            'offset' => 2,
            'limit' => 1,
            'expected' => [new TestEntity('2', 'b')],
        ];
        yield [
            'offset' => 1,
            'limit' => 2,
            'expected' => [new TestEntity('1', 'a'), new TestEntity('2', 'b')],
        ];
        yield [
            'offset' => 1,
            'limit' => 10,
            'expected' => [new TestEntity('1', 'a'), new TestEntity('2', 'b'), new TestEntity('3', 'c')],
        ];
        /* yield [
             'offset' => 1,
             'limit' => 10,
             'expected' => [[2], [3]],
         ];*/
        yield [
            'offset' => 5,
            'limit' => 10,
            'expected' => [],
        ];
        yield [
            'offset' => 1,
            'limit' => 0,
            'expected' => [],
        ];
    }
}
