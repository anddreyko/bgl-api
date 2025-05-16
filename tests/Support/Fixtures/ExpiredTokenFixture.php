<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Contexts\Auth\Entities\User;
use App\Contexts\Auth\Repositories\DbUserRepository as UserRepository;
use App\Core\Components\Database\Fixtures\DbFixture;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

final class ExpiredTokenFixture extends DbFixture
{
    public const UUID = Uuid::NIL;

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new UserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            id: new Id(self::UUID),
            email: new Email('expired-token@app.test'),
            hash: new PasswordHash(self::UUID),
            token: new Token(self::UUID, $date->modify('-1 day')),
            createdAt: $date
        );

        $users->add($user);

        $manager->flush();
    }
}
