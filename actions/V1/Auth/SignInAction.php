<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\IdentificationForm;
use App\Auth\Services\IdentificationService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignInCest
 */
final class SignInAction extends BaseAction
{
    public function __construct(
        private readonly IdentificationService $service,
        private readonly ValidatorInterface $validator,
        readonly ResponseFactoryInterface $factory
    ) {
        parent::__construct($factory);
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/login-by-email",
     *     @OA\Response(
     *         response="200",
     *         description="Logging by email"
     *     )
     * )
     */
    public function content(): Response
    {
        $form = new IdentificationForm((string)$this->getParam('email'), (string)$this->getParam('password'));
        $this->validator->validate($form);

        $this->service->handle($form);

        return new Response(data: ['token_access' => 'token-access', 'token_update' => 'token-update'], result: true);
    }
}
