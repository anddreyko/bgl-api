<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use Actions\BaseAction;
use App\Application\Middlewares\AuthorizationMiddleware;
use App\Contexts\Auth\Entities\User;
use App\Contexts\Auth\Forms\SignOutForm;
use App\Contexts\Auth\Services\SignOutService;
use App\Core\Components\Http\Entities\Response;
use App\Core\ValueObjects\WebToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see \Tests\Api\V1\Auth\SignOutCest
 */
final class SignOutAction extends BaseAction
{
    public function __construct(
        private readonly SignOutService $service,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function content(): Response
    {
        /** @var User $identity */
        $identity = $this->getAttribute(AuthorizationMiddleware::ATTRIBUTE_IDENTITY);
        /** @var WebToken $token */
        $token = $this->getAttribute(AuthorizationMiddleware::ATTRIBUTE_TOKEN);

        $form = new SignOutForm($identity, $token);
        $this->validator->validate($form);

        $this->service->handle($form);

        return new Response(data: 'sign out', result: true);
    }
}
