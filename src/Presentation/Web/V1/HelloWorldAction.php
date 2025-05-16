<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1;

use App\Infrastructure\Http\Entities\Response;
use App\Infrastructure\Localization\Translator;
use App\Presentation\Web\BaseAction;

/**
 * @see \Tests\Acceptance\HelloWorldCest
 */
final class HelloWorldAction extends BaseAction
{
    public function __construct(
        private readonly Translator $translator
    ) {
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/hello-world",
     *     @OA\Response(
     *         response="200",
     *         description="Hello world"
     *     )
     * )
     */
    public function content(): Response
    {
        return new Response(data: $this->translator->trans(id: 'Hello world!', domain: 'hello-world'), result: true);
    }
}
