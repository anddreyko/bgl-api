<?php

declare(strict_types=1);

namespace App\Core\ValueObjects;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Webmozart\Assert\Assert;

/**
 * @see \Tests\Unit\Auth\ValueObjects\WebTokenTest
 */
#[Embeddable]
final class WebToken
{
    #[Column(type: Types::STRING, nullable: false)]
    private string $value;

    public function __construct(string $value)
    {
        $value = \trim($value);
        Assert::notEmpty($value);
        $this->value = \trim($value);
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
