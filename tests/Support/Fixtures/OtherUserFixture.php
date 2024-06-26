<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
use App\Auth\Repositories\DbUserRepository as UserRepository;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Fixtures\DbFixture;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use Doctrine\ORM\EntityManagerInterface;

final class OtherUserFixture extends DbFixture
{
    public const UUID = '55555555-5555-5555-5555-555555555555';
    public const EMAIL = 'other-user@app.test';
    public const PASS = 'password';
    public const HASH = '$argon2i$v=19$m=65536,t=4,p=1$aWpLR3FaMFVYZGlUODJXWg$8gmO8pfngi1MxYWCcMSMuf/yyI/mrIlvPevYyjUHkG4';

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new UserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            id: new Id(self::UUID),
            email: new Email(self::EMAIL),
            hash: new PasswordHash(self::HASH),
            token: Token::create($date->modify('+1 day')),
            createdAt: $date
        );

        $user->setStatus(UserStatusEnum::Active);

        $users->add($user);

        $manager->flush();
    }
}
