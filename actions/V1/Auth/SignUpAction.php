<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Services\Register\RegistrationByEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class SignUpAction extends BaseAction
{
    public function __construct(
        private readonly ResponseFactoryInterface $factory,
        private readonly RegistrationByEmailService $service
    ) {
        parent::__construct($this->factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array{email?: string, password?: string} $params */
        $params = $request->getQueryParams();
        $this->service->handle(new RegistrationByEmailForm($params['email'] ?? '', $params['password'] ?? ''));

        return parent::handle($request);
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/register-by-email",
     *     @OA\Response(
     *         response="200",
     *         description="Register by email"
     *     )
     * )
     */
    public function content(): Response
    {
        return new Response(data: 'Confirm the specified email', result: true);
    }
}
