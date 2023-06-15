<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Auth\Entities\User;
use App\Auth\Repositories\DbUserRepository as UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Fixtures\DbFixture;
use Doctrine\ORM\EntityManagerInterface;

final class NotActiveUserFixture extends DbFixture
{
    public const UUID = '11111111-1111-1111-1111-111111111111';
    public const TOKEN_2 = '22222222-2222-2222-2222-222222222222';
    public const TOKEN_EXPIRED = '33333333-3333-3333-3333-333333333333';
    public const EMAIL = 'new-user@app.test';

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new UserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            id: new Id(self::UUID),
            email: new Email(self::EMAIL),
            hash: new PasswordHash(self::UUID),
            token: new Token(self::UUID, $date->modify('+1 day')),
            createdAt: $date
        );

        $user->setTokenConfirm(new Token(self::TOKEN_2, $date->modify('+1 day')));
        $user->setTokenConfirm(new Token(self::TOKEN_EXPIRED, $date->modify('-1 day')));

        $users->add($user);

        $manager->flush();
    }
}
