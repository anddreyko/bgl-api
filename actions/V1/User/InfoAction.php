<?php

declare(strict_types=1);

namespace Actions\V1\User;

use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Id;
use App\Core\Exceptions\NotFoundException;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;

/**
 * @see \Tests\Api\V1\User\InfoCest
 */
final class InfoAction extends BaseAction
{
    public function content(): Response
    {
        try {
            $id = new Id($this->getArgs('id'));
        } catch (\Exception $exception) {
            throw new NotFoundException(previous: $exception);
        }

        /** @var UserRepository $users */
        $users = $this->getContainer(UserRepository::class);

        $user = $users->getById($id);

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
