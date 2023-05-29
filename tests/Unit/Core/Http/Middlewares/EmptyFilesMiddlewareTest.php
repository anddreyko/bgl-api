<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Middlewares;

use App\Core\Http\Middlewares\EmptyFilesMiddleware;
use Codeception\Test\Unit;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;

/**
 * @covers \App\Core\Http\Middlewares\EmptyFilesMiddleware
 */
final class EmptyFilesMiddlewareTest extends Unit
{
    public function testSuccess(): void
    {
        $middleware = new EmptyFilesMiddleware();

        $file = (new UploadedFileFactory())->createUploadedFile(
            (new StreamFactory())->createStream(''),
            0
        );
        $emptyFile = (new UploadedFileFactory())->createUploadedFile(
            (new StreamFactory())->createStream(''),
            0,
            UPLOAD_ERR_NO_FILE
        );

        $request = (new ServerRequestFactory())->createServerRequest('POST', 'http://app.test')
            ->withUploadedFiles([
                'file' => $file,
                'emptyFile' => $emptyFile,
                'files' => [$file, $emptyFile],
            ]);

        $handler = $this->makeEmpty(
            RequestHandlerInterface::class,
            [
                'handle' => function (ServerRequestInterface $request) use ($file) {
                    self::assertEquals(
                        ['file' => $file, 'files' => [$file]],
                        $request->getUploadedFiles()
                    );

                    return (new ResponseFactory())->createResponse();
                },
            ]
        );

        $middleware->process($request, $handler);
    }
}
