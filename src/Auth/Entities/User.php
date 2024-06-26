<?php

declare(strict_types=1);

namespace App\Auth\Entities;

use App\Auth\Enums\UserStatusEnum;
use App\Auth\Types\EmailType;
use App\Auth\Types\IdType;
use App\Auth\Types\PasswordHashType;
use App\Auth\Types\StatusType;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Auth\ValueObjects\WebToken;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * @see \Tests\Unit\Auth\Entities\UserTest
 * @OA\Schema()
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'auth_user')]
class User
{
    private function __construct(
        /** @OA\Property(type="string") */
        #[ORM\Id]
        #[ORM\Column(type: IdType::NAME)]
        private Id $id,
        /** @OA\Property(type="string") */
        #[ORM\Column(type: EmailType::NAME, unique: true)]
        private Email $email,
        #[ORM\Column(type: PasswordHashType::NAME, nullable: false)]
        private PasswordHash $hash,
        /** @OA\Property(type="string") */
        #[ORM\Column(type: StatusType::NAME)]
        private UserStatusEnum $status = UserStatusEnum::Wait,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => 'CURRENT_TIMESTAMP'])]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserTokenConfirm::class, cascade: ['all'], orphanRemoval: true)]
        private Collection $tokenConfirm = new ArrayCollection(),
        #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserTokenAccess::class, cascade: ['all'], orphanRemoval: true)]
        private Collection $tokenAccess = new ArrayCollection(),
    ) {
    }

    public static function createByEmail(
        Id $id,
        Email $email,
        PasswordHash $hash,
        Token $token = null,
        \DateTimeImmutable $createdAt = new \DateTimeImmutable()
    ): self {
        $user = new self(id: $id, email: $email, hash: $hash, createdAt: $createdAt);

        if ($token) {
            $user->setTokenConfirm($token);
        }

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

    /**
     * @return Token[]
     */
    public function getTokenConfirm(): array
    {
        return $this->tokenConfirm
            ->map(static fn(UserTokenConfirm $token) => $token->getToken())
            ->toArray();
    }

    public function setTokenConfirm(Token $tokenConfirm): void
    {
        $this->tokenConfirm->add(new UserTokenConfirm($this, $tokenConfirm));
    }

    public function removeTokenConfirm(?Token $token = null): void
    {
        if ($token) {
            $this->tokenConfirm->removeElement($token);
        } else {
            $this->tokenConfirm->clear();
        }
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

    public function setTokenAccess(WebToken $token): self
    {
        $this->tokenAccess->add(new UserTokenAccess($this, $token));

        return $this;
    }

    /**
     * @return WebToken[]
     */
    public function getTokenAccess(): array
    {
        return $this->tokenAccess
            ->map(static fn(UserTokenAccess $token) => $token->getToken())
            ->toArray();
    }

    public function removeTokenAccess(int | string $key): void
    {
        $this->tokenAccess->remove($key);
    }
}
