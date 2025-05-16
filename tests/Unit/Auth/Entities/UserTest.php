<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Entities;

use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Enums\UserStatusEnum;
use Codeception\Test\Unit;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @covers \App\Domain\Auth\Entities\User
 */
class UserTest extends Unit
{
    private User $user;
    private Id $id;
    private \DateTimeImmutable $date;
    private PasswordHash $hash;
    private Email $email;
    private ArrayCollection $token;
    private UserStatusEnum $status;
    private WebToken $access1;
    private WebToken $access2;

    protected function _setUp(): void
    {
        $this->id = Id::create();
        $this->date = new \DateTimeImmutable();
        $this->email = new Email('test@mail.test');
        $this->hash = new PasswordHash('secret');
        $this->token = new ArrayCollection();
        $token = Token::create(new \DateTimeImmutable());
        $this->token->add($token);
        $this->status = UserStatusEnum::Wait;
        $this->access1 = new WebToken('access-1');
        $this->access2 = new WebToken('access-2');

        $this->user = User::createByEmail(
            id: $this->id,
            email: $this->email,
            hash: $this->hash,
            token: $token,
            createdAt: $this->date
        );
        $this->user->setTokenAccess($this->access1);
        $this->user->setTokenAccess($this->access2);

        parent::_setUp();
    }

    public function testId(): void
    {
        $this->assertEquals($this->id->getValue(), $this->user->getId()->getValue());
    }

    public function testDate(): void
    {
        $this->assertEquals($this->date->getTimestamp(), $this->user->getCreatedAt()->getTimestamp());
    }

    public function testEmail(): void
    {
        $this->assertEquals($this->email->getValue(), $this->user->getEmail()->getValue());
    }

    public function testPasswordHash(): void
    {
        $this->assertEquals($this->hash->getValue(), $this->user->getHash()->getValue());
    }

    public function testToken(): void
    {
        $this->assertEquals(
            $this->token->getValues(),
            $this->user->getTokenConfirm()
        );
    }

    public function testStatus(): void
    {
        $this->assertEquals($this->status, $this->user->getStatus());
    }

    public function testIsActive(): void
    {
        $this->assertFalse($this->user->isActive());
    }

    public function testIsWait(): void
    {
        $this->assertTrue($this->user->isWait());
    }

    public function testSettingStatus(): void
    {
        $user = $this->user->setStatus(UserStatusEnum::Active);
        $this->assertEquals(UserStatusEnum::Active, $user->getStatus());
    }

    public function testAccessToken(): void
    {
        $this->assertEquals([$this->access1, $this->access2], $this->user->getTokenAccess());
    }
}
