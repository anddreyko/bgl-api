<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command as CreateCommand;
use Bgl\Application\Handlers\Mates\CreateMate\Handler as CreateHandler;
use Bgl\Application\Handlers\Mates\GetMate\Handler;
use Bgl\Application\Handlers\Mates\GetMate\Query;
use Bgl\Application\Handlers\Mates\GetMate\Result;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Mates\GetMate\Handler
 */
#[Group('application', 'handler', 'mates', 'get-mate')]
final class GetMateCest
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

    public function testGetExistingMate(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0001', name: 'Ivan', notes: 'Notes'),
            messageId: 'msg-1',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Query(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0001', mateId: $created->id),
            messageId: 'msg-2',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('Ivan', $result->name);
        $i->assertSame('Notes', $result->notes);
    }

    public function testGetNonExistentMateThrows(FunctionalTester $i): void
    {
        $i->expectThrowable(
            new NotFoundException('Mate not found'),
            function (): void {
                ($this->handler)(new Envelope(
                    message: new Query(userId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaa0001', mateId: '00000000-0000-4000-8000-000000000099'),
                    messageId: 'msg-3',
                ));
            },
        );
    }

    public function testGetOtherUserMateThrows(FunctionalTester $i): void
    {
        $created = ($this->createHandler)(new Envelope(
            message: new CreateCommand(userId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbb0001', name: 'Ivan'),
            messageId: 'msg-4',
        ));

        $i->expectThrowable(
            new NotFoundException('Mate not found'),
            function () use ($created): void {
                ($this->handler)(new Envelope(
                    message: new Query(userId: 'cccccccc-cccc-4ccc-8ccc-cccccccc0001', mateId: $created->id),
                    messageId: 'msg-5',
                ));
            },
        );
    }
}
