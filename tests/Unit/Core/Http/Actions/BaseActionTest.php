<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Actions;

use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \App\Presentation\Web\BaseAction
 */
final class BaseActionTest extends Unit
{
    public function testInvoke(): void
    {
        $content = new Response('content');

        $action = $this->make(
            BaseAction::class,
            ['content' => Expected::atLeastOnce($content)]
        );
        $request = $this->makeEmpty(
            ServerRequestInterface::class,
            ['getQueryParams' => static fn() => ['param-1' => 'test']]
        );
        $response = (new HttpFactory())->createResponse();

        $this->assertEquals(
            json_encode($content, JSON_THROW_ON_ERROR),
            $action->__invoke($request, $response, ['foo' => 'bar'])->getBody()
        );

        $this->assertEquals('bar', $action->getArgs('foo'));

        $this->assertEquals('test', $action->getParam('param-1'));

        $this->assertEquals($content, $action->content());
    }
}
