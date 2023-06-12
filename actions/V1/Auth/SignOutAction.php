<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Entities\User;
use App\Auth\Forms\SignOutForm;
use App\Auth\Services\SignOutService;
use App\Auth\ValueObjects\WebToken;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\Http\Middlewares\AuthorizationMiddleware;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignOutCest
 */
final class SignOutAction extends BaseAction
{
    public function content(): Response
    {
        /** @var User $identity */
        $identity = $this->getAttribute(AuthorizationMiddleware::ATTRIBUTE_IDENTITY);
        /** @var WebToken $token */
        $token = $this->getAttribute(AuthorizationMiddleware::ATTRIBUTE_TOKEN);

        $form = new SignOutForm($identity, $token);

        /** @var ValidatorInterface $validator */
        $validator = $this->getContainer(ValidatorInterface::class);
        $validator->validate($form);

        /** @var SignOutService $service */
        $service = $this->getContainer(SignOutService::class);
        $service->handle($form);

        return new Response(data: 'sign out', result: true);
    }
}
