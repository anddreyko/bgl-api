<?php

declare(strict_types=1);

namespace Actions\V1\User;

use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;

final class InfoAction extends BaseAction
{
    public function content(): Response
    {
        return new Response(data: 'user\'s info', result: true);
    }
}
