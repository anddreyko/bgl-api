<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Renderers;

use App\Core\Http\Renderers\JsonErrorRenderer;
use App\Core\Validation\Exceptions\ValidationException;
use Codeception\Test\Unit;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;

/**
 * @covers \App\Core\Http\Renderers\JsonErrorRenderer
 */
final class JsonErrorRendererTest extends Unit
{
    public function testSimple(): void
    {
        $render = new JsonErrorRenderer();

        $json = $render->__invoke(new \Exception(), false);

        $this->assertEquals(
            json_encode(
                ['message' => 'Unexpected error.', 'result' => false],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ),
            $json
        );
    }

    public function testDisplayError(): void
    {
        $render = new JsonErrorRenderer();

        $json = $render->__invoke(new \Exception(), true);

        $this->assertEquals(
            json_encode(
                [
                    'message' => 'Unexpected error.',
                    'result' => false,
                    'exception' => [
                        [
                            'type' => 'Exception',
                            'code' => 0,
                            'message' => '',
                            'file' => __FILE__,
                            'line' => 37,
                        ],
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ),
            $json
        );
    }

    public function testHttpException(): void
    {
        $render = new JsonErrorRenderer();

        $request = $this->makeEmpty(ServerRequestInterface::class);
        $exception = new HttpException($request);
        $exception->setTitle('Http error');
        $json = $render->__invoke($exception, true);

        $this->assertEquals(
            json_encode(
                [
                    'message' => 'Http error',
                    'result' => false,
                    'exception' => [
                        [
                            'type' => HttpException::class,
                            'code' => 0,
                            'message' => '',
                            'file' => __FILE__,
                            'line' => 65,
                        ],
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ),
            $json
        );
    }

    public function testValidationException(): void
    {
        $render = new JsonErrorRenderer();

        $validationException = new ValidationException(['token' => 'Incorrect token', 'email' => 'Incorrect email']);

        $request = $this->makeEmpty(ServerRequestInterface::class);
        $exception = new HttpException($request, 'Some error', 1, $validationException);
        $exception->setTitle('Http error');
        $json = $render->__invoke($exception, true);

        $this->assertEquals(
            json_encode(
                [
                    'message' => 'Http error',
                    'result' => false,
                    'exception' => [
                        [
                            'type' => HttpException::class,
                            'code' => 1,
                            'message' => 'Some error',
                            'file' => __FILE__,
                            'line' => 97,
                        ],
                        [
                            'type' => ValidationException::class,
                            'code' => 0,
                            'message' => '',
                            'file' => __FILE__,
                            'line' => 94,
                        ],
                    ],
                    'errors' => [
                        'token' => 'Incorrect token',
                        'email' => 'Incorrect email',
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ),
            $json
        );
    }
}
