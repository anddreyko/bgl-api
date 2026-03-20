<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SendVerification;

use Bgl\Core\Messages\Message;
use Bgl\Core\ValueObjects\Email;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /** @var non-empty-string */
    public string $email;

    public function __construct(
        string $email,
    ) {
        new Email($email);

        /** @var non-empty-string $email */
        $this->email = $email;
    }
}
