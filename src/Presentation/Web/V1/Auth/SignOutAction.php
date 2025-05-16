<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Auth;

use App\Application\Middleware\AuthorizationMiddleware;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Forms\SignOutForm;
use App\Domain\Auth\Services\SignOutService;
use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;
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
