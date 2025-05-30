<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\User;

use App\Core\Exceptions\NotFoundException;
use App\Core\ValueObjects\Id;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;
use OpenApi\Annotations as OA;

/**
 * @see \Tests\Api\V1\User\InfoCest
 */
final class InfoAction extends BaseAction
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/v1/user/{id}",
     *     security={"bearerAuth":{}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User's id.",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Get information about this user.",
     *         @OA\Schema(type="User"),
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
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found",
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
        try {
            $id = new Id($this->getArgs('id'));
        } catch (\Exception $exception) {
            throw new NotFoundException(previous: $exception);
        }

        $user = $this->users->getById($id);

        return new Response(
            data: [
                'id' => $user->getId()->getValue(),
                'email' => $user->getEmail()->getValue(),
                'is_active' => $user->getStatus()->isActive(),
            ],
            result: true
        );
    }
}
