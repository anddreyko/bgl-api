<?php

declare(strict_types=1);

namespace Actions\V1;

use Actions\BaseAction;
use App\Core\Components\Http\Entities\Response;
use App\Core\Components\Localization\Translator;

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
