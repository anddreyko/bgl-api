<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use Actions\BaseAction;
use App\Core\Components\Http\Entities\Response;

final class RefreshAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(data: 'refresh', result: true);
    }
}
