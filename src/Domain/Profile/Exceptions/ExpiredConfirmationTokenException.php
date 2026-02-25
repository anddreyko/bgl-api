<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Exceptions;

final class ExpiredConfirmationTokenException extends \DomainException
{
    protected $message = 'Confirmation token has expired.';
}
