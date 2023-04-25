<?php

declare(strict_types=1);

namespace Actions\V1;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;

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
        return new Response(data: 'Hello world!', result: true);
    }
}
