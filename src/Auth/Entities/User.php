<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\Enums\UserStatusEnum;
use App\Auth\Types\EmailType;
use App\Auth\Types\IdType;
use App\Auth\Types\PasswordHashType;
use App\Auth\Types\StatusType;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Auth\ValueObjects\WebToken;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @see \Tests\Unit\Auth\Entities\UserTest
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'auth_user')]
final class User
{
    #[ORM\Column(type: PasswordHashType::NAME, nullable: true)]
    private ?PasswordHash $hash = null;
    #[ORM\Embedded(class: Token::class)]
    private ?Token $token = null;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAccessToken::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $accessTokens;

    private function __construct(
        #[ORM\Column(type: IdType::NAME)]
        #[ORM\Id]
        private readonly Id $id,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => 'CURRENT_TIMESTAMP'])]
        private readonly \DateTimeImmutable $createdAt,
        #[ORM\Column(type: EmailType::NAME, unique: true)]
        private readonly Email $email,
        #[ORM\Column(type: StatusType::NAME)]
        private UserStatusEnum $status,
    ) {
        $this->accessTokens = new ArrayCollection();
    }

    public static function createByEmail(
        Id $id,
        \DateTimeImmutable $createdAt,
        Email $email,
        PasswordHash $hash,
        Token $token
    ): self {
        $user = new self($id, $createdAt, $email, UserStatusEnum::Wait);
        $user->hash = $hash;
        $user->token = $token;

        return $user;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getHash(): ?PasswordHash
    {
        return $this->hash;
    }

    public function setHash(PasswordHash $hash): void
    {
        $this->hash = $hash;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function setStatus(UserStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isWait(): bool
    {
        return $this->status->isWait();
    }

    public function setAccessTokens(UserAccessToken $token): void
    {
        $this->accessTokens->add($token);
    }

    /**
     * @return WebToken[]
     */
    public function getAccessTokens(): array
    {
        return $this->accessTokens
            ->map(static fn(UserAccessToken $token) => $token->getToken())
            ->toArray();
    }

    #[ORM\PostLoad]
    public function postload(): void
    {
        if ($this->token && $this->token->isEmpty()) {
            $this->token = null;
        }
    }
}
