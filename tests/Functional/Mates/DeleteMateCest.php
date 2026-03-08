<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command as CreateCommand;
use Bgl\Application\Handlers\Mates\CreateMate\Handler as CreateHandler;
use Bgl\Application\Handlers\Mates\DeleteMate\Command;
use Bgl\Application\Handlers\Mates\DeleteMate\Handler;
use Bgl\Application\Handlers\Mates\GetMate\Handler as GetHandler;
use Bgl\Application\Handlers\Mates\GetMate\Query;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Mates\DeleteMate\Handler
 */
#[Group('application', 'handler', 'mates', 'delete-mate')]
final class DeleteMateCest
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
            message: new CreateCommand(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0001', name: 'Ivan'),
            messageId: 'msg-1',
        ));

        ($this->handler)(new Envelope(
            message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0001', mateId: $created->id),
            messageId: 'msg-2',
        ));

        // After soft delete, get should throw Not Found
        $i->expectThrowable(
            new NotFoundException('Mate not found'),
            function () use ($created): void {
                ($this->getHandler)(new Envelope(
                    message: new Query(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0001', mateId: $created->id),
                    messageId: 'msg-3',
                ));
            },
        );
    }

    public function testDeleteOtherUserMateThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0002', name: 'Ivan'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new NotFoundException('Mate not found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0003', mateId: $created->id),
                    messageId: 'msg-5',
                ));
            },
        );
    }

    public function testDeleteNonExistentMateThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Mate not found'),
            function (): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeee0001', mateId: '00000000-0000-4000-8000-000000000099'),
                    messageId: 'msg-6',
                ));
            },
        );
    }
}
