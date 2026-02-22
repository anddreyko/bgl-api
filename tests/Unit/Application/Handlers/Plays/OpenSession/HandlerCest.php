<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Plays\OpenSession;

use Bgl\Application\Handlers\Plays\OpenSession\Command;
use Bgl\Application\Handlers\Plays\OpenSession\Handler;
use Bgl\Application\Handlers\Plays\OpenSession\Result;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Plays\Entities\Sessions;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use Psr\Clock\ClockInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\OpenSession\Handler
 */
#[Group('plays', 'open-session')]
final class HandlerCest
{
    public function testSuccessfulSessionOpening(UnitTester $i): void
    {
        $sessions = Stub::makeEmpty(Sessions::class, [
            'add' => static function (): void {
            },
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-06-15 20:00:00'),
        ]);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class, [
            'generate' => static fn(): Uuid => new Uuid('generated-uuid'),
        ]);

        $handler = new Handler($sessions, $uuidGenerator, $clock);

        $command = new Command(userId: 'user-123', name: 'Game night');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->sessionId);
    }

    public function testSessionOpeningWithoutName(UnitTester $i): void
    {
        $sessions = Stub::makeEmpty(Sessions::class, [
            'add' => static function (): void {
            },
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-06-15 20:00:00'),
        ]);

        $uuidGenerator = Stub::makeEmpty(UuidGenerator::class, [
            'generate' => static fn(): Uuid => new Uuid('generated-uuid'),
        ]);

        $handler = new Handler($sessions, $uuidGenerator, $clock);

        $command = new Command(userId: 'user-456');
        $envelope = new Envelope($command, 'msg-2');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->sessionId);
    }
}
