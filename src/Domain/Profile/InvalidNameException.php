<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

final class InvalidNameException extends \DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(\sprintf('Invalid name: "%s". Only alphanumeric characters are allowed.', $name));
    }
}
