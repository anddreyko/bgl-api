<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Locations;

use Bgl\Application\Handlers\Locations\CreateLocation\Command as CreateCommand;
use Bgl\Application\Handlers\Locations\CreateLocation\Handler as CreateHandler;
use Bgl\Application\Handlers\Locations\UpdateLocation\Command;
use Bgl\Application\Handlers\Locations\UpdateLocation\Handler;
use Bgl\Application\Handlers\Locations\UpdateLocation\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Domain\Locations\LocationAlreadyExistsException;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Locations\UpdateLocation\Handler
 */
#[Group('application', 'handler', 'locations', 'update-location')]
final class UpdateLocationCest
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

    public function testSuccessfulUpdate(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(
                userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0010',
                name: 'Old Cafe',
                address: 'Old St',
            ),
            messageId: 'msg-1',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0010',
                locationId: $created->id,
                name: 'New Cafe',
                address: 'New St',
                notes: 'Updated',
                url: 'https://new.com',
            ),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('New Cafe', $result->name);
        $i->assertSame('New St', $result->address);
        $i->assertSame('Updated', $result->notes);
        $i->assertSame('https://new.com', $result->url);
    }

    public function testUpdateToExistingNameThrows(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0020', name: 'Cafe A'),
            messageId: 'msg-3',
        ));

        $created2 = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0020', name: 'Cafe B'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new LocationAlreadyExistsException(),
            function () use ($created2): void {
                ($this->handler)(new Envelope(
                    message: new Command(
                        userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0020',
                        locationId: $created2->id,
                        name: 'Cafe A',
                    ),
                    messageId: 'msg-5',
                ));
            },
        );
    }

    public function testUpdateOtherUserLocationThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0030', name: 'My Cafe'),
            messageId: 'msg-6',
        ));

        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Command(
                        userId: 'dddddddd-dddd-4ddd-8ddd-dddddddd0040',
                        locationId: $created->id,
                        name: 'New Name',
                    ),
                    messageId: 'msg-7',
                ));
            },
        );
    }
}
