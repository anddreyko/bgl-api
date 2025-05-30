<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Entities;

use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Entities\UserTokenConfirm;
use Codeception\Test\Unit;

/**
 * @covers \App\Domain\Auth\Entities\UserTokenConfirm
 */
class UserTokenConfirmTest extends Unit
{
    private User $user;
    private Id $id;
    private PasswordHash $hash;
    private Email $email;
    private Token $token;
    private UserTokenConfirm $confirm;

    protected function _setUp(): void
    {
        $this->id = Id::create();
        $this->email = new Email('test@mail.test');
        $this->hash = new PasswordHash('secret');
        $this->token = Token::create(new \DateTimeImmutable());

        $this->user = User::createByEmail(
            id: $this->id,
            email: $this->email,
            hash: $this->hash,
            token: $this->token
        );

        $this->confirm = new UserTokenConfirm($this->user, $this->token);

        parent::_setUp();
    }

    public function testUser(): void
    {
        $this->assertEquals($this->user, $this->confirm->getUser());
    }

    public function testToken(): void
    {
        $this->assertEquals($this->token, $this->confirm->getToken());
    }
}
