<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Authentification\OpenAuth;

use Bgl\Core\ValueObjects\Uuid;
use League\OAuth2\Server\Entities\UserEntityInterface;

final readonly class UserId implements UserEntityInterface
{
    public function __construct(private Uuid $uuid)
    {
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return $this->uuid->getValue() ?? throw new \RuntimeException('Unexpected error');
    }
}
