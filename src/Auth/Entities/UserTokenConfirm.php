<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\ValueObjects\Token;
use Doctrine\ORM\Mapping as ORM;

/**
 * @see \Tests\Unit\Auth\Entities\UserTokenConfirmTest
 */
#[ORM\Entity]
#[ORM\Table(
    name: 'auth_user_confirm',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uidx__user_id__token_value', columns: ['user_id', 'token_value']),
        new ORM\UniqueConstraint(name: 'idx__user_id', columns: ['user_id']),
        new ORM\UniqueConstraint(name: 'idx__token_value', columns: ['token_value']),
    ]
)]
final readonly class UserTokenConfirm
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokenConfirm')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,
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
