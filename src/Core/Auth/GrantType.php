<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

enum GrantType: string
{
    case Passkey = 'passkey';
    case Credential = 'credential';
}
