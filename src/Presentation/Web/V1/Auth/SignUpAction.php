<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Auth;

use App\Domain\Auth\Forms\RegistrationByEmailForm;
use App\Domain\Auth\Services\Register\RegistrationByEmailService;
use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class SignUpAction extends BaseAction
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly RegistrationByEmailService $registrationService
    ) {
    }

    /**
     * @OpenApi\Annotations\Post(
     *     path="/v1/auth/sign-up-by-email",
     *     @OA\Parameter(
     *         name="email",
     *         in="body",
     *         description="User's email.",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="body",
     *         description="User's password.",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Register by email",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal error",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Invalid parameters",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Invalid parameters",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function content(): Response
    {
        $form = new RegistrationByEmailForm((string)$this->getParam('email'), (string)$this->getParam('password'));

        $this->validator->validate($form);

        $this->registrationService->handle($form);

        return new Response(data: 'Confirm the specified email', result: true);
    }
}
