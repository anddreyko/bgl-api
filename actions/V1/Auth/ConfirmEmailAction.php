<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\ConfirmationEmailForm;
use App\Auth\Services\Register\ConfirmationEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\Validation\Services\ValidationService;

/**
 * @see \Tests\Api\V1\Auth\ConfirmEmailCest
 */
final class ConfirmEmailAction extends BaseAction
{
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
        /** @var ValidationService $validator */
        $validator = $this->getContainer(ValidationService::class);
        $validator->validate($form);

        /** @var ConfirmationEmailService $confirmationService */
        $confirmationService = $this->getContainer(ConfirmationEmailService::class);
        $confirmationService->handle($form);

        return new Response(data: 'Specified email is confirmed', result: true);
    }
}
