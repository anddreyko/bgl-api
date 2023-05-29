<?php

declare(strict_types=1);

namespace App\Core\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @see \Tests\Unit\Core\Http\Middlewares\EmptyFilesMiddlewareTest
 */
final class EmptyFilesMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withUploadedFiles($this->handleFiles($request->getUploadedFiles())));
    }

    /**
     * @param array<array-key, mixed> $files
     *
     * @return array<array-key, mixed>
     */
    private function handleFiles(array $files): array
    {
        $result = [];

        /** @var UploadedFileInterface|UploadedFileInterface[] $file */
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFileInterface && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $result[$key] = $file;
            } elseif (is_array($file)) {
                $result[$key] = $this->handleFiles($file);
            }
        }

        return $result;
    }
}
