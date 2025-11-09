<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Core\Messages\Message;
use Bgl\Domain\Auth\Entities\User;

/**
 * @extends Message<User>
 */
interface Command extends Message
{
    public function getUsername(): string;

    public function getPassword(): string;
}
