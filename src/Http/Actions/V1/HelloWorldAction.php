<?php

declare(strict_types=1);

namespace App\Http\Actions\V1;

use App\Http\Entities\Response;

class HelloWorldAction extends BaseAction
{
    /**
     * @OA\Get(
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
