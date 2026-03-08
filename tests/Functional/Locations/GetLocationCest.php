<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Locations;

use Bgl\Application\Handlers\Locations\CreateLocation\Command as CreateCommand;
use Bgl\Application\Handlers\Locations\CreateLocation\Handler as CreateHandler;
use Bgl\Application\Handlers\Locations\GetLocation\Handler;
use Bgl\Application\Handlers\Locations\GetLocation\Query;
use Bgl\Application\Handlers\Locations\GetLocation\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Locations\GetLocation\Handler
 */
#[Group('application', 'handler', 'locations', 'get-location')]
final class GetLocationCest
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

    public function testGetExistingLocation(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0010', name: 'Cafe', address: '123 St'),
            messageId: 'msg-1',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0010', locationId: $created->id),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('Cafe', $result->name);
        $i->assertSame('123 St', $result->address);
    }

    public function testGetNonExistentLocationThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function (): void {
                ($this->handler)(new Envelope(
                    message: new Query(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0010', locationId: '00000000-0000-4000-8000-000000000099'),
                    messageId: 'msg-3',
                ));
            },
        );
    }

    public function testGetOtherUserLocationThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbb0010', name: 'Private Cafe'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Query(userId: 'cccccccc-cccc-4ccc-8ccc-cccccccc0010', locationId: $created->id),
                    messageId: 'msg-5',
                ));
            },
        );
    }
}
