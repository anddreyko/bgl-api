<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Entities;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use Codeception\Test\Unit;

/**
 * @covers \App\Auth\Entities\User
 */
class UserTest extends Unit
{
    private ?User $user = null;
    private Id $id;
    private \DateTimeImmutable $date;
    private PasswordHash $hash;
    private Email $email;
    private Token $token;
    private UserStatusEnum $status;

    protected function _setUp(): void
    {
        $this->id = Id::create();
        $this->date = new \DateTimeImmutable();
        $this->email = new Email('test@mail.test');
        $this->hash = new PasswordHash('secret');
        $this->token = Token::create(new \DateTimeImmutable());
        $this->status = UserStatusEnum::wait();

        $this->user = new User(
            id: $this->id,
            date: $this->date,
            email: $this->email,
            hash: $this->hash,
            token: $this->token,
            status: $this->status
        );

        parent::_setUp();
    }

    public function testId(): void
    {
        $this->assertEquals($this->id->getValue(), $this->user->getId()->getValue());
    }

    public function testDate(): void
    {
        $this->assertEquals($this->date->getTimestamp(), $this->user->getDate()->getTimestamp());
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
        $this->assertEquals($this->token->getValue(), $this->user->getToken()->getValue());
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
}
