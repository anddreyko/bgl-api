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
        private readonly IdentificationService $authenticationService,
        private readonly ValidatorInterface $validator,
        readonly ResponseFactoryInterface $factory
    ) {
        parent::__construct($factory);
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/sign-in-by-email",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email.",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
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
        $form = new IdentificationForm((string)$this->getParam('email'), (string)$this->getParam('password'));
        $this->validator->validate($form);

        return new Response(data: ['token_access' => $this->authenticationService->handle($form)], result: true);
    }
}
