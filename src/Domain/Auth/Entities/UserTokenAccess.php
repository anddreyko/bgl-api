<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Types\WebTokenType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @see \Tests\Unit\Auth\Entities\UserTokenAccessTest
 */
#[ORM\Entity]
#[ORM\Table(
    name: 'auth_user_access',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uidx__user_id__token', columns: ['user_id', 'token']),
        new ORM\UniqueConstraint(name: 'idx__user_id', columns: ['user_id']),
        new ORM\UniqueConstraint(name: 'idx__token', columns: ['token']),
    ]
)]
final readonly class UserTokenAccess
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokenAccess')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Id]
        #[ORM\Column(type: WebTokenType::NAME, unique: true)]
        private WebToken $token,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
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
