<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Locations;

use Bgl\Application\Handlers\Locations\CreateLocation\Command as CreateCommand;
use Bgl\Application\Handlers\Locations\CreateLocation\Handler as CreateHandler;
use Bgl\Application\Handlers\Locations\ListLocations\Handler;
use Bgl\Application\Handlers\Locations\ListLocations\Query;
use Bgl\Application\Handlers\Locations\ListLocations\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Locations\ListLocations\Handler
 */
#[Group('application', 'handler', 'locations', 'list-locations')]
final class ListLocationsCest
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
            message: new Query(userId: '11111111-1111-4111-8111-111111111100'),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame([], $result->data);
        $i->assertSame(0, $result->total);
    }

    public function testListsUsersLocationsOnly(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '22222222-2222-4222-8222-222222222200', name: 'Cafe A'),
            messageId: 'msg-2',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '22222222-2222-4222-8222-222222222200', name: 'Cafe B'),
            messageId: 'msg-3',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '33333333-3333-4333-8333-333333333300', name: 'Other'),
            messageId: 'msg-4',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: '22222222-2222-4222-8222-222222222200'),
            messageId: 'msg-5',
        ));

        $i->assertSame(2, $result->total);
        $i->assertCount(2, $result->data);
    }

    public function testPagination(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '66666666-6666-4666-8666-666666666600', name: 'Place A'),
            messageId: 'msg-page-1',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '66666666-6666-4666-8666-666666666600', name: 'Place B'),
            messageId: 'msg-page-2',
        ));
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: '66666666-6666-4666-8666-666666666600', name: 'Place C'),
            messageId: 'msg-page-3',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: '66666666-6666-4666-8666-666666666600', page: 1, size: 2),
            messageId: 'msg-page-4',
        ));

        $i->assertSame(3, $result->total);
        $i->assertCount(2, $result->data);
        $i->assertSame(1, $result->page);
        $i->assertSame(2, $result->size);
    }
}
