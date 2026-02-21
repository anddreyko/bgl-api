<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Repositories;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final readonly class TestEntity
{
    public function __construct(
        #[ORM\Id, ORM\Column(type: Types::STRING)]
        private string $id,
        #[ORM\Column(type: Types::STRING)]
        private string $value,
        #[ORM\Column(type: Types::INTEGER, nullable: true)]
        private ?int $status = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }
}
