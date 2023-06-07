<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;

/**
 * @see \Tests\Api\V1\Auth\SignInCest
 */
final class SignOutAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(data: 'sign out', result: true);
    }
}
