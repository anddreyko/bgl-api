<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Http\AuthParams;
use Bgl\Core\Http\ParamMap;
use Bgl\Core\Messages\Message;
use Bgl\Presentation\Api\Interceptors\Interceptor;

final readonly class CompiledOperation
{
    /**
     * @param class-string<Message> $messageClass
     * @param list<class-string<Interceptor>> $interceptors
     * @param array<string, mixed> $openApiSchema
     */
    public function __construct(
        public string $messageClass,
        public array $interceptors = [],
        public AuthParams $authParams = new AuthParams(),
        public ParamMap $paramMap = new ParamMap(),
        public array $openApiSchema = [],
    ) {
    }
}
