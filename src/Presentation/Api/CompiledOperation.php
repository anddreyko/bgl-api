<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Messages\Message;
use Bgl\Presentation\Api\Interceptors\Interceptor;

final readonly class CompiledOperation
{
    /**
     * @param class-string<Message> $messageClass
     * @param list<class-string<Interceptor>> $interceptors
     * @param list<string> $authParams
     * @param array<string, string> $paramMap
     * @param array<string, mixed> $openApiSchema
     */
    public function __construct(
        public string $messageClass,
        public array $interceptors = [],
        public array $authParams = [],
        public array $paramMap = [],
        public array $openApiSchema = [],
    ) {
    }
}
