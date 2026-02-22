<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Messages\Message;
use Bgl\Presentation\Api\Interceptors\Interceptor;

final readonly class MatchedOperation
{
    /**
     * @param class-string<Message> $messageClass
     * @param list<class-string<Interceptor>> $interceptors
     * @param array<string, string> $pathParams
     * @param array<string, mixed> $schema
     */
    public function __construct(
        public string $messageClass,
        public array $interceptors = [],
        public array $pathParams = [],
        public array $schema = [],
    ) {
    }
}
