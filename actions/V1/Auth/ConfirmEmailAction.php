<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use Actions\BaseAction;
use App\Contexts\Auth\Forms\ConfirmationEmailForm;
use App\Contexts\Auth\Services\Register\ConfirmationEmailService;
use App\Core\Components\Http\Entities\Response;
use App\Core\Components\Validation\Validator;

/**
 * @see \Tests\Api\V1\Auth\ConfirmEmailCest
 */
final class ConfirmEmailAction extends BaseAction
{
    public function __construct(
        private readonly ConfirmationEmailService $confirmationService,
        private readonly Validator $validator
    ) {
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/confirm-by-email/{token}",
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Confirmation token from email.",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirm email",
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
        $form = new ConfirmationEmailForm($this->getArgs('token'));
        $this->validator->validate($form);

        $this->confirmationService->handle($form);

        return new Response(data: 'Specified email is confirmed', result: true);
    }
}
