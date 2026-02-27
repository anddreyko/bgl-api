<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command as CreateCommand;
use Bgl\Application\Handlers\Mates\CreateMate\Handler as CreateHandler;
use Bgl\Application\Handlers\Mates\DeleteMate\Command;
use Bgl\Application\Handlers\Mates\DeleteMate\Handler;
use Bgl\Application\Handlers\Mates\GetMate\Handler as GetHandler;
use Bgl\Application\Handlers\Mates\GetMate\Query;
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
            message: new CreateCommand(userId: 'user-del', name: 'Ivan'),
            messageId: 'msg-1',
        ));

        ($this->handler)(new Envelope(
            message: new Command(userId: 'user-del', mateId: $created->id),
            messageId: 'msg-2',
        ));

        // After soft delete, get should throw Not Found
        $i->expectThrowable(
            new \DomainException('Not Found'),
            function () use ($created): void {
                ($this->getHandler)(new Envelope(
                    message: new Query(userId: 'user-del', mateId: $created->id),
                    messageId: 'msg-3',
                ));
            },
        );
    }

    public function testDeleteOtherUserMateThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-own2', name: 'Ivan'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new \DomainException('Not Found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'user-other2', mateId: $created->id),
                    messageId: 'msg-5',
                ));
            },
        );
    }

    public function testDeleteNonExistentMateThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new \DomainException('Not Found'),
            function (): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'user-del', mateId: 'non-existent'),
                    messageId: 'msg-6',
                ));
            },
        );
    }
}
