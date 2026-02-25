<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Plays;

use Bgl\Application\Handlers\Plays\OpenSession\Command;
use Bgl\Application\Handlers\Plays\OpenSession\Handler;
use Bgl\Application\Handlers\Plays\OpenSession\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Plays\OpenSession\Handler
 */
#[Group('application', 'handler', 'plays', 'open-session')]
final class OpenSessionCest
{
    private EntityManagerInterface $em;
    private Handler $handler;
    private Plays $plays;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var Handler $handler */
        $this->handler = $container->get(Handler::class);

        /** @var Plays $plays */
        $this->plays = $container->get(Plays::class);
    }

    public function testSuccessfulPlayOpening(FunctionalTester $i): void
    {
        $result = ($this->handler)(new Envelope(
            message: new Command(userId: 'user-123', name: 'Game night'),
            messageId: 'msg-1',
        ));

        $this->em->flush();

        $i->assertInstanceOf(Result::class, $result);
        $i->assertNotEmpty($result->sessionId);
        $i->assertNotNull($this->plays->find($result->sessionId));
    }
}
