<?php

declare(strict_types=1);

namespace Fixtures;

use App\Auth\Entities\User;
use App\Auth\Repositories\UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Fixtures\DbFixture;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

final class UserFixture extends DbFixture
{
    private const HASH = '123';

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new UserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            new Id(Uuid::NIL),
            $date,
            new Email('admin@4records.com'),
            new PasswordHash(self::HASH),
            Token::create($date->modify('+1 day'))
        );

        $users->activateUser($user);

        $manager->flush();
    }
}
