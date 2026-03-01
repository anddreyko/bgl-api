<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\Register;

use Bgl\Core\Messages\Message;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Password;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    public string $email;
    public string $password;

    public function __construct(
        string $email,
        string $password,
        public ?string $name = null,
    ) {
        new Email($email);
        new Password($password);

        $this->email = $email;
        $this->password = $password;
    }
}
