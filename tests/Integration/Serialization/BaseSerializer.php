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
        $data = $result->toArray();

        $i->assertIsArray($data);
        $i->assertEquals('1', $data['id']);
        $i->assertEquals('John Doe', $data['value']);
        $i->assertEquals(2, $data['status']);
    }

    public function testSerializeObjectWithNullField(IntegrationTester $i): void
    {
        $dto = new TestEntity(
            id: '25',
            value: 'Jane Doe',
            status: null,
        );

        $result = $this->serializer()->serialize($dto);
        $data = $result->toArray();

        $i->assertIsArray($data);
        $i->assertEquals('25', $data['id']);
        $i->assertEquals('Jane Doe', $data['value']);
        $i->assertNull($data['status']);
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
        $data = $result->toArray();

        $i->assertIsArray($data);
        $i->assertEquals('1', $data['message_id']);
        $i->assertEquals('2', $data['parent_id']);
        $i->assertEquals('3', $data['trace_id']);
        $i->assertEquals('test', $data['environment']);
        $i->assertEquals('version', $data['version']);
        $i->assertEquals('2025-12-31T00:00:00+00:00', $data['datetime']['datetime']);
        $i->assertEquals('1767139200', $data['datetime']['timestamp']);
        $i->assertEquals('P7DT5S', $data['delay']['interval']);
        $i->assertEquals(604805, $data['delay']['seconds']);
    }
}
