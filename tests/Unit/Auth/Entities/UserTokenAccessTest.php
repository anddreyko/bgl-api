<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Entities;

use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Entities\UserTokenAccess;
use App\Domain\Auth\Enums\UserStatusEnum;
use Codeception\Test\Unit;

/**
 * @covers \App\Domain\Auth\Entities\UserTokenAccess
 */
class UserTokenAccessTest extends Unit
{
    private User $user;
    private Id $id;
    private \DateTimeImmutable $date;
    private PasswordHash $hash;
    private Email $email;
    private Token $token;
    private UserStatusEnum $status;
    private WebToken $webToken;
    private UserTokenAccess $access;

    protected function _setUp(): void
    {
        $this->id = Id::create();
        $this->date = new \DateTimeImmutable();
        $this->email = new Email('test@mail.test');
        $this->hash = new PasswordHash('secret');
        $this->token = Token::create(new \DateTimeImmutable());
        $this->status = UserStatusEnum::Wait;

        $this->user = User::createByEmail(
            id: $this->id,
            email: $this->email,
            hash: $this->hash,
            token: $this->token,
            createdAt: $this->date
        );
        $this->webToken = new WebToken('access-1');

        $this->access = new UserTokenAccess($this->user, $this->webToken, $this->date);

        parent::_setUp();
    }

    public function testUser(): void
    {
        $this->assertEquals($this->user, $this->access->getUser());
    }

    public function testWebToken(): void
    {
        $this->assertEquals($this->webToken, $this->access->getToken());
    }

    public function testDate(): void
    {
        $this->assertEquals($this->date, $this->access->getCreatedAt());
    }
}
