<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\LogInForm;
use App\Auth\Services\LogInService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignInCest
 */
final class SignInAction extends BaseAction
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LogInService $authenticationService,
    ) {
    }

    /**
     * @OpenApi\Annotations\Post(
     *     path="/v1/auth/sign-in-by-email",
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
     *         description="Token access",
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
        $form = new LogInForm((string)$this->getParam('email'), (string)$this->getParam('password'));
        $this->validator->validate($form);

        return new Response(data: ['token_access' => $this->authenticationService->handle($form)], result: true);
    }
}
