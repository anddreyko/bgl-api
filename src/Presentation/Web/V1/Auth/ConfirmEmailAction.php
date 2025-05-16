<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Auth;

use App\Domain\Auth\Forms\ConfirmationEmailForm;
use App\Domain\Auth\Services\Register\ConfirmationEmailService;
use App\Infrastructure\Http\Entities\Response;
use App\Infrastructure\Validation\Validator;
use App\Presentation\Web\BaseAction;

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
