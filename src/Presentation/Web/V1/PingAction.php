<?php

declare(strict_types=1);

namespace App\Presentation\Web\V1;

use App\Infrastructure\Http\Entities\Response;
use App\Presentation\Web\BaseAction;

final class PingAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(time());
    }
}
