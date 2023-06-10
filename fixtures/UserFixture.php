<?php

declare(strict_types=1);

namespace Fixtures;

use App\Auth\Entities\User;
use App\Auth\Repositories\DbUserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Fixtures\DbFixture;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

final class UserFixture extends DbFixture
{
    private const HASH = Uuid::NIL;

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new DbUserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            id: new Id(Uuid::NIL),
            email: new Email('admin@4records.com'),
            hash: new PasswordHash(self::HASH),
            token: Token::create($date->modify('+1 day')),
            createdAt: $date
        );
        $users->activateUser($user);

        $user = User::createByEmail(
            id: new Id(Uuid::NAMESPACE_X500),
            email: new Email('waiting@4records.com'),
            hash: new PasswordHash(self::HASH),
        );
        $users->persist($user);

        $manager->flush();
    }
}
