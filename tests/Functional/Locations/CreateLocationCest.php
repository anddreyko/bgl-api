<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Locations;

use Bgl\Application\Handlers\Locations\CreateLocation\Command;
use Bgl\Application\Handlers\Locations\CreateLocation\Handler;
use Bgl\Application\Handlers\Locations\CreateLocation\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Domain\Locations\LocationAlreadyExistsException;
use Bgl\Domain\Locations\Locations;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Locations\CreateLocation\Handler
 */
#[Group('application', 'handler', 'locations', 'create-location')]
final class CreateLocationCest
{
    private Handler $handler;
    private Locations $locations;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Locations $locations */
        $this->locations = $container->get(Locations::class);
    }

    public function testSuccessfulLocationCreation(FunctionalTester $i): void
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $result = ($this->handler)(new Envelope(
            message: new Command(
                userId: $uid,
                name: 'Board Game Cafe',
                address: '123 Main St',
                notes: 'Great atmosphere',
                url: 'https://example.com',
            ),
            messageId: 'msg-1',
        ));

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->id);
        $i->assertSame('Board Game Cafe', $result->name);
        $i->assertSame('123 Main St', $result->address);
        $i->assertSame('Great atmosphere', $result->notes);
        $i->assertSame('https://example.com', $result->url);
        $i->assertNotEmpty($result->createdAt);
        $i->assertNotNull($this->locations->find($result->id));
    }

    public function testCreateLocationWithNullOptionalFields(FunctionalTester $i): void
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $result = ($this->handler)(new Envelope(
            message: new Command(userId: $uid, name: 'Home'),
            messageId: 'msg-2',
        ));

        $i->assertNull($result->address);
        $i->assertNull($result->notes);
        $i->assertNull($result->url);
    }

    public function testCreateDuplicateNameThrows(FunctionalTester $i): void
    {
        $uid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        ($this->handler)(new Envelope(
            message: new Command(userId: $uid, name: 'Duplicate'),
            messageId: 'msg-3',
        ));

        $i->expectThrowable(
            new LocationAlreadyExistsException(),
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
        $uid1 = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $uid2 = \Ramsey\Uuid\Uuid::uuid4()->toString();

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
