<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Helpers;

use App\Infrastructure\Http\Helpers\HttpHelper;
use Codeception\Test\Unit;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * @covers \App\Infrastructure\Http\Helpers\HttpHelper
 */
class HttpHelperTest extends Unit
{
    private ResponseFactoryInterface $factory;

    protected function _before(): void
    {
        $this->factory = new ResponseFactory();
        parent::_before();
    }

    /**
     * @param mixed $source
     * @param mixed $exceptSource
     * @param int $exceptStatus
     *
     * @return void
     * @throws \JsonException
     * @dataProvider getCases
     */
    public function testResponse(mixed $source, string $exceptSource, int $exceptStatus): void
    {
        $response = HttpHelper::json($this->factory->createResponse($exceptStatus), $source);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals($exceptSource, (string)$response->getBody());
        self::assertEquals($exceptStatus, $response->getStatusCode());
    }

    public static function getCases(): array
    {
        $fullObject = new \stdClass();
        $fullObject->content = 'Hello';

        return [
            'null' => [null, 'null', 200],
            'true' => [true, 'true', 200],
            'false' => [false, 'false', 200],
            'int' => [42, '42', 200],
            'intAsString' => ['42', '"42"', 200],
            'string' => ['Hello', '"Hello"', 200],
            'object' => [new \stdClass(), '{}', 201],
            'fullObject' => [$fullObject, '{"content":"Hello"}', 404],
            'objectAsArray' => [['content' => 'Hello'], '{"content":"Hello"}', 200],
            'trueArray' => [['apple', 'orange'], '["apple","orange"]', 200],
        ];
    }
}
