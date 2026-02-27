<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Mates;

use Bgl\Application\Handlers\Mates\CreateMate\Command;
use Bgl\Application\Handlers\Mates\CreateMate\Handler;
use Bgl\Application\Handlers\Mates\CreateMate\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Mates\CreateMate\Handler
 */
#[Group('application', 'handler', 'mates', 'create-mate')]
final class CreateMateCest
{
    private Handler $handler;
    private Mates $mates;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Mates $mates */
        $this->mates = $container->get(Mates::class);
    }

    public function testSuccessfulMateCreation(FunctionalTester $i): void
    {
        $uid = 'create-success-' . uniqid();
        $result = ($this->handler)(new Envelope(
            message: new Command(userId: $uid, name: 'Ivan', notes: 'Likes Carcassonne'),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);
        $i->assertSame('Ivan', $result->name);
        $i->assertSame('Likes Carcassonne', $result->notes);
        $i->assertNotEmpty($result->createdAt);
        $i->assertNotNull($this->mates->find($result->id));
    }

    public function testCreateMateWithNullNotes(FunctionalTester $i): void
    {
        $uid = 'create-null-' . uniqid();
        $result = ($this->handler)(new Envelope(
            message: new Command(userId: $uid, name: 'Anna'),
            messageId: 'msg-2',
        ));

        $i->assertNull($result->notes);
    }

    public function testCreateDuplicateNameThrows(FunctionalTester $i): void
    {
        $uid = 'create-dup-' . uniqid();
        ($this->handler)(new Envelope(
            message: new Command(userId: $uid, name: 'Duplicate'),
            messageId: 'msg-3',
        ));

        $i->expectThrowable(
            new \DomainException('Mate with this name already exists'),
            function () use ($uid): void {
                ($this->handler)(new Envelope(
                    message: new Command(userId: $uid, name: 'duplicate'),
                    messageId: 'msg-4',
                ));
            },
        );
    }

    public function testDuplicateNameDifferentUserAllowed(FunctionalTester $i): void
    {
        $uid1 = 'create-diff1-' . uniqid();
        $uid2 = 'create-diff2-' . uniqid();

        ($this->handler)(new Envelope(
            message: new Command(userId: $uid1, name: 'SharedName'),
            messageId: 'msg-5',
        ));

        $result = ($this->handler)(new Envelope(
            message: new Command(userId: $uid2, name: 'SharedName'),
            messageId: 'msg-6',
        ));

        $i->assertSame('SharedName', $result->name);
    }
}
