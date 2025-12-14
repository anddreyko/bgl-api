<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Authentification\OpenAuth;

enum GrantType: string
{
    case Passkey = 'passkey';
    case Credential = 'credential';
}
