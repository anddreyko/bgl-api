<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\ValueObjects\Token;
use Doctrine\ORM\Mapping as ORM;

/**
 * @see \Tests\Unit\Auth\Entities\UserTokenConfirmTest
 */
#[ORM\Entity]
#[ORM\Table(name: 'auth_user_confirm', uniqueConstraints: [new ORM\UniqueConstraint(columns: ['user_id'])])]
final readonly class UserTokenConfirm
{
    public function __construct(
        #[ORM\Id]
        #[ORM\OneToOne(inversedBy: 'tokenConfirm', targetEntity: User::class)]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Id]
        #[ORM\Embedded(class: Token::class)]
        private Token $token,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): Token
    {
        return $this->token;
    }
}
