<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Serialization;

use Bgl\Application\Handlers\Ping\Result;
use Bgl\Core\Serialization\Serializer;
use Bgl\Core\ValueObjects\DateInterval;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Tests\Support\IntegrationTester;
use Bgl\Tests\Support\Repositories\TestEntity;

abstract class BaseSerializer
{
    abstract protected function serializer(): Serializer;

    public function testSerializeSimpleObject(IntegrationTester $i): void
    {
        $dto = new TestEntity(
            id: '1',
            value: 'John Doe',
            status: 2
        );

        $result = $this->serializer()->serialize($dto);

        $i->assertIsArray($result);
        $i->assertEquals('1', $result['id']);
        $i->assertEquals('John Doe', $result['value']);
        $i->assertEquals(2, $result['status']);
    }

    public function testSerializeObjectWithNullField(IntegrationTester $i): void
    {
        $dto = new TestEntity(
            id: '25',
            value: 'Jane Doe',
            status: null,
        );

        $result = $this->serializer()->serialize($dto);

        $i->assertIsArray($result);
        $i->assertEquals('25', $result['id']);
        $i->assertEquals('Jane Doe', $result['value']);
        $i->assertNull($result['status']);
    }

    public function testSerializeNestedObject(IntegrationTester $i): void
    {
        $dto = new Result(
            datetime: new DateTime(new \DateTimeImmutable('2025-12-31 00:00:00', new \DateTimeZone('UTC'))),
            delay: new DateInterval(new \DateInterval('P7DT5S')),
            version: 'version',
            environment: (string)getenv('APP_ENV'),
            messageId: '1',
            parentId: '2',
            traceId: '3'
        );

        $result = $this->serializer()->serialize($dto);

        $i->assertIsArray($result);
        $i->assertEquals('1', $result['message_id']);
        $i->assertEquals('2', $result['parent_id']);
        $i->assertEquals('3', $result['trace_id']);
        $i->assertEquals('test', $result['environment']);
        $i->assertEquals('version', $result['version']);
        $i->assertEquals('2025-12-31T00:00:00+00:00', $result['datetime']['datetime']);
        $i->assertEquals('1767139200', $result['datetime']['timestamp']);
        $i->assertEquals('P7DT5S', $result['delay']['interval']);
        $i->assertEquals(604805, $result['delay']['seconds']);
    }
}
