<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Renderers;

use App\Infrastructure\Validation\ValidationException;
use Slim\Error\AbstractErrorRenderer;
use Throwable;

/**
 * @see \Tests\Unit\Core\Http\Renderers\JsonErrorRendererTest
 */
final class JsonErrorRenderer extends AbstractErrorRenderer
{
    protected string $defaultErrorTitle = 'Unexpected error.';

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        /** @var array{
         *     message: string,
         *     result: bool,
         *     exception: Throwable[],
         *     errors?: string[]
         * } $error
         */
        $error = [
            'message' => $this->getErrorTitle($exception),
            'result' => false,
            'code' => $exception->getPrevious()?->getCode() ?? $exception->getCode(),
        ];

        if ($displayErrorDetails) {
            $error['exception'] = [];
            do {
                if ($exception instanceof ValidationException && !isset($error['errors'])) {
                    $error['errors'] = $exception->getErrors();
                }

                $error['exception'][] = $this->formatExceptionFragment($exception);
            } while ($exception = $exception->getPrevious());
        }

        return (string)json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<string|int>
     */
    private function formatExceptionFragment(Throwable $exception): array
    {
        return [
            'type' => get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
    }
}
