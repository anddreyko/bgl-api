<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Services\Register\RegistrationByEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class SignUpAction extends BaseAction
{
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

        /** @var ValidatorInterface $validator */
        $validator = $this->getContainer(ValidatorInterface::class);
        $validator->validate($form);

        /** @var RegistrationByEmailService $registrationService */
        $registrationService = $this->getContainer(RegistrationByEmailService::class);
        $registrationService->handle($form);

        return new Response(data: 'Confirm the specified email', result: true);
    }
}
