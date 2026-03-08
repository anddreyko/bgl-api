<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Locations;

use Bgl\Application\Handlers\Locations\CreateLocation\Command as CreateCommand;
use Bgl\Application\Handlers\Locations\CreateLocation\Handler as CreateHandler;
use Bgl\Application\Handlers\Locations\DeleteLocation\Command;
use Bgl\Application\Handlers\Locations\DeleteLocation\Handler;
use Bgl\Application\Handlers\Locations\GetLocation\Handler as GetHandler;
use Bgl\Application\Handlers\Locations\GetLocation\Query;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Locations\DeleteLocation\Handler
 */
#[Group('application', 'handler', 'locations', 'delete-location')]
final class DeleteLocationCest
{
    private Handler $handler;
    private CreateHandler $createHandler;
    private GetHandler $getHandler;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var CreateHandler $createHandler */
        $this->createHandler = $container->get(CreateHandler::class);

        /** @var GetHandler $getHandler */
        $this->getHandler = $container->get(GetHandler::class);
    }

    public function testSuccessfulSoftDelete(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0010', name: 'Cafe'),
            messageId: 'msg-1',
        ));

        ($this->handler)(new Envelope(
            message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0010', locationId: $created->id),
            messageId: 'msg-2',
        ));

        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function () use ($created): void {
                ($this->getHandler)(new Envelope(
                    message: new Query(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0010', locationId: $created->id),
                    messageId: 'msg-3',
                ));
            },
        );
    }

    public function testDeleteOtherUserLocationThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0020', name: 'Cafe'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0030', locationId: $created->id),
                    messageId: 'msg-5',
                ));
            },
        );
    }

    public function testDeleteNonExistentLocationThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Location not found'),
            function (): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0010', locationId: '00000000-0000-4000-8000-000000000099'),
                    messageId: 'msg-6',
                ));
            },
        );
    }
}
