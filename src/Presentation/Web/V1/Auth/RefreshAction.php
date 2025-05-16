<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1\Auth;

use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;

final class RefreshAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(data: 'refresh', result: true);
    }
}
