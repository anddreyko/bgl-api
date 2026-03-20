<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

enum CredentialType: string
{
    case Code = 'code';
    case Token = 'token';
}
