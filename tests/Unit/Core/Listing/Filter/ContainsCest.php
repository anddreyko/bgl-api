<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Listing\Filter;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\FilterVisitor;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Listing\Filter\Contains
 */
#[Group('core', 'listing', 'filter', 'contains')]
final class ContainsCest
{
    public function testConstructorStoresLeftAndRight(UnitTester $i): void
    {
        $left = new Field('name');
        $right = 'search-term';

        $filter = new Contains($left, $right);

        $i->assertSame($left, $filter->left);
        $i->assertSame($right, $filter->right);
    }

    public function testAcceptCallsContainsOnVisitor(UnitTester $i): void
    {
        $filter = new Contains(new Field('title'), 'query');

        $visitor = new ContainsTestVisitor();

        $result = $filter->accept($visitor);

        $i->assertTrue($visitor->called);
        $i->assertSame($filter, $visitor->received);
        $i->assertSame('visited', $result);
    }
}

/**
 * @implements FilterVisitor<string|null>
 */
final class ContainsTestVisitor implements FilterVisitor
{
    public bool $called = false;
    public ?Contains $received = null;

    #[\Override]
    public function all(All $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function equals(Equals $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function less(Less $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function greater(Greater $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function and(AndX $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function or(OrX $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function not(Not $filter): mixed
    {
        return null;
    }

    #[\Override]
    public function contains(Contains $filter): mixed
    {
        $this->called = true;
        $this->received = $filter;

        return 'visited';
    }
}
