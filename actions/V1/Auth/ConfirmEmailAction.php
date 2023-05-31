<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\ConfirmationEmailForm;
use App\Auth\Services\Register\ConfirmationEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class ConfirmEmailAction extends BaseAction
{
    public function __construct(
        private readonly ConfirmationEmailService $service,
        readonly ResponseFactoryInterface $factory
    ) {
        parent::__construct($factory);
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
        $this->service->handle(new ConfirmationEmailForm((string)$this->getParam('token')));

        return new Response(data: 'Specified email is confirmed', result: true);
    }
}
