<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command as CreateCommand;
use Bgl\Application\Handlers\Mates\CreateMate\Handler as CreateHandler;
use Bgl\Application\Handlers\Mates\ListMates\Handler;
use Bgl\Application\Handlers\Mates\ListMates\Query;
use Bgl\Application\Handlers\Mates\ListMates\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Mates\ListMates\Handler
 */
#[Group('application', 'handler', 'mates', 'list-mates')]
final class ListMatesCest
{
    private Handler $handler;
    private CreateHandler $createHandler;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var CreateHandler $createHandler */
        $this->createHandler = $container->get(CreateHandler::class);
    }

    public function testEmptyList(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'user-empty'),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame([], $result->data);
        $i->assertSame(0, $result->total);
    }

    public function testListsUsersMatesOnly(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-list', name: 'Ivan'),
            messageId: 'msg-2',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-list', name: 'Anna'),
            messageId: 'msg-3',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-other', name: 'Other'),
            messageId: 'msg-4',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'user-list'),
            messageId: 'msg-5',
        ));

        $i->assertSame(2, $result->total);
        $i->assertCount(2, $result->data);
    }

    public function testListSortedByNameAsc(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-sort', name: 'Zara'),
            messageId: 'msg-sort-1',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-sort', name: 'Anna'),
            messageId: 'msg-sort-2',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'user-sort', sort: 'name', order: 'asc'),
            messageId: 'msg-sort-3',
        ));

        $i->assertSame(2, $result->total);
        $i->assertSame('Anna', $result->data[0]['name']);
        $i->assertSame('Zara', $result->data[1]['name']);
    }

    public function testListSortedByNameDesc(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-sort-desc', name: 'Zara'),
            messageId: 'msg-sdesc-1',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-sort-desc', name: 'Anna'),
            messageId: 'msg-sdesc-2',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'user-sort-desc', sort: 'name', order: 'desc'),
            messageId: 'msg-sdesc-3',
        ));

        $i->assertSame(2, $result->total);
        $i->assertSame('Zara', $result->data[0]['name']);
        $i->assertSame('Anna', $result->data[1]['name']);
    }

    public function testPagination(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-page', name: 'Alice'),
            messageId: 'msg-page-1',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-page', name: 'Bob'),
            messageId: 'msg-page-2',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-page', name: 'Charlie'),
            messageId: 'msg-page-3',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'user-page', page: 1, size: 2),
            messageId: 'msg-page-4',
        ));

        $i->assertSame(3, $result->total);
        $i->assertCount(2, $result->data);
        $i->assertSame(1, $result->page);
        $i->assertSame(2, $result->size);
    }
}
