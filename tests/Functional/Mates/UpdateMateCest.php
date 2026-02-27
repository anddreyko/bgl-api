<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command as CreateCommand;
use Bgl\Application\Handlers\Mates\CreateMate\Handler as CreateHandler;
use Bgl\Application\Handlers\Mates\UpdateMate\Command;
use Bgl\Application\Handlers\Mates\UpdateMate\Handler;
use Bgl\Application\Handlers\Mates\UpdateMate\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Mates\UpdateMate\Handler
 */
#[Group('application', 'handler', 'mates', 'update-mate')]
final class UpdateMateCest
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
            message: new CreateCommand(userId: 'user-upd', name: 'Ivan', notes: 'Old'),
            messageId: 'msg-1',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Command(userId: 'user-upd', mateId: $created->id, name: 'Ivan Petrov', notes: 'New'),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('Ivan Petrov', $result->name);
        $i->assertSame('New', $result->notes);
    }

    public function testUpdateToExistingNameThrows(FunctionalTester $i): void
    {
        ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-upd2', name: 'Ivan'),
            messageId: 'msg-3',
        ));

        $created2 = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-upd2', name: 'Anna'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new \DomainException('Mate with this name already exists'),
            function () use ($created2): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'user-upd2', mateId: $created2->id, name: 'Ivan'),
                    messageId: 'msg-5',
                ));
            },
        );
    }

    public function testUpdateOtherUserMateThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'user-own', name: 'Ivan'),
            messageId: 'msg-6',
        ));

        $i->expectThrowable(
            new \DomainException('Not Found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: 'user-other', mateId: $created->id, name: 'New Name'),
                    messageId: 'msg-7',
                ));
            },
        );
    }
}
