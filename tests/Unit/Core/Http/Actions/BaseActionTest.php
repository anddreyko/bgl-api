<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Actions;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * @covers \App\Core\Http\Actions\BaseAction
 */
final class BaseActionTest extends Unit
{
    public function testHandle(): void
    {
        $content = new Response('content');
        $response = new HttpFactory();

        $action = $this->construct(
            BaseAction::class,
            ['factory' => $response],
            ['content' => Expected::once($content)]
        );
        $request = $this->createStub(ServerRequest::class);

        $this->assertEquals(json_encode($content, JSON_THROW_ON_ERROR), $action->handle($request)->getBody());
    }
}
