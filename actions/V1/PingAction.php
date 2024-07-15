<?php

declare(strict_types=1);

namespace Actions\V1;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;

final class PingAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(time());
    }
}
