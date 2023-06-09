<?php

declare(strict_types=1);

namespace App\Auth\ValueObjects;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Webmozart\Assert\Assert;

#[Embeddable]
final class WebToken
{
    /** @var string */
    #[Column(type: Types::STRING, nullable: true)]
    private $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        Assert::notEmpty($value);
        $this->value = trim($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
