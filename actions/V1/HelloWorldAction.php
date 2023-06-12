<?php

declare(strict_types=1);

namespace Actions\V1;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\Localization\Services\TranslatorService;

/**
 * @see \Tests\Acceptance\HelloWorldCest
 */
final class HelloWorldAction extends BaseAction
{
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
        /** @var TranslatorService $translator */
        $translator = $this->getContainer(TranslatorService::class);

        return new Response(data: $translator->trans(id: 'Hello world!', domain: 'hello-world'), result: true);
    }
}
