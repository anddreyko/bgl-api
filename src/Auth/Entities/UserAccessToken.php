<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\Types\WebTokenType;
use App\Auth\ValueObjects\WebToken;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'auth_user_access_token', uniqueConstraints: [
    new ORM\UniqueConstraint(columns: ['user_id', 'token']),
])]
final readonly class UserAccessToken
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accessTokens')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Id]
        #[ORM\Column(type: WebTokenType::NAME, unique: true)]
        private WebToken $token,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => 'CURRENT_TIMESTAMP'])]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): WebToken
    {
        return $this->token;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
