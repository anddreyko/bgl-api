<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Persistence;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Domain\Plays\Play;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineFilter;
use Bgl\Tests\Benchmark\DoctrineBenchHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class DoctrineFilterBench
{
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        DoctrineBenchHelper::createSchema();
        $this->em = DoctrineBenchHelper::entityManager();
    }

    public function tearDown(): void
    {
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchBuildEqualsQuery(): void
    {
        $qb = $this->em->createQueryBuilder()->select('p')->from(Play::class, 'p');
        $filter = new Equals(new Field('userId'), 'bench-user');

        $condition = $filter->accept(new DoctrineFilter($qb, 'p'));
        $qb->andWhere($condition);
        $qb->getDQL();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchBuildContainsQuery(): void
    {
        $qb = $this->em->createQueryBuilder()->select('p')->from(Play::class, 'p');
        $filter = new Contains(new Field('name'), 'Play 5');

        $condition = $filter->accept(new DoctrineFilter($qb, 'p'));
        $qb->andWhere($condition);
        $qb->getDQL();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchBuildNestedQuery(): void
    {
        $qb = $this->em->createQueryBuilder()->select('p')->from(Play::class, 'p');
        $filter = new AndX([
            new Equals(new Field('userId'), 'bench-user'),
            new OrX([
                new Contains(new Field('name'), 'Play 1'),
                new Contains(new Field('name'), 'Play 2'),
            ]),
        ]);

        $condition = $filter->accept(new DoctrineFilter($qb, 'p'));
        $qb->andWhere($condition);
        $qb->getDQL();
    }
}
