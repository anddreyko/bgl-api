<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\ConfirmationEmailForm;
use App\Auth\Services\Register\ConfirmationEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class ConfirmEmailAction extends BaseAction
{
    public function __construct(
        private readonly ResponseFactoryInterface $factory,
        private readonly ConfirmationEmailService $service
    ) {
        parent::__construct($this->factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->service->handle(new ConfirmationEmailForm((string)($request->getQueryParams()['token'] ?? '')));

        return parent::handle($request);
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/confirm-email",
     *     @OA\Response(
     *         response="200",
     *         description="Confirm email"
     *     )
     * )
     */
    public function content(): Response
    {
        return new Response(data: 'Specified email is confirmed', result: true);
    }
}
