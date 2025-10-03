<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Messages;

use Bgl\Core\Messages\EnvelopeFactory;
use Bgl\Tests\Support\FunctionalTester;
use Bgl\Tests\Support\Messages\Ping;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Messages\EnvelopeFactory
 */
#[Group('messages')]
class EnvelopeFactoryCest
{
    public function testBuild(FunctionalTester $i): void
    {
        $envelopeFactory = new EnvelopeFactory();

        $message1 = new Ping('message 1');
        $messageId1 = '01';
        $envelope1 = $envelopeFactory->build($message1, $messageId1);

        $message2 = new Ping('message 2');
        $messageId2 = '02';
        $envelope2 = $envelopeFactory->build($message2, $messageId2, $envelope1);

        $message3 = new Ping('message 3');
        $messageId3 = '03';
        $envelope3 = $envelopeFactory->build($message3, $messageId3, $envelope2);

        $i->assertEquals('message 1', $envelope1->message->text);
        $i->assertEquals($messageId1, $envelope1->messageId);
        $i->assertEquals(null, $envelope1->parentId);
        $i->assertEquals($messageId1, $envelope1->traceId);

        $i->assertEquals('message 2', $envelope2->message->text);
        $i->assertEquals($messageId2, $envelope2->messageId);
        $i->assertEquals($messageId1, $envelope2->parentId);
        $i->assertEquals($messageId1, $envelope2->traceId);

        $i->assertEquals('message 3', $envelope3->message->text);
        $i->assertEquals($messageId3, $envelope3->messageId);
        $i->assertEquals($messageId2, $envelope3->parentId);
        $i->assertEquals($messageId1, $envelope3->traceId);
    }
}
