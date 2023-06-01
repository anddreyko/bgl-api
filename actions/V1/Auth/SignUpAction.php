<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Services\Register\RegistrationByEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class SignUpAction extends BaseAction
{
    public function __construct(
        private readonly RegistrationByEmailService $service,
        private readonly ValidatorInterface $validator,
        readonly ResponseFactoryInterface $factory
    ) {
        parent::__construct($factory);
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
        $form = new RegistrationByEmailForm((string)$this->getParam('email'), (string)$this->getParam('password'));
        $this->validator->validate($form);

        $this->service->handle($form);

        return new Response(data: 'Confirm the specified email', result: true);
    }
}
