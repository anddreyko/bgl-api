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

final class ExistedUserFixture extends DbFixture
{
    private const UUID = '22222222-2222-2222-2222-222222222222';
    public const EMAIL = 'existed-user@app.test';

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new UserRepository($manager);

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            new Id(self::UUID),
            $date,
            new Email(self::EMAIL),
            new PasswordHash(self::UUID),
            Token::create($date->modify('+1 day'))
        );

        $users->add($user);

        $manager->flush();
    }
}