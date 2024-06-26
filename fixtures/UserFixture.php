<?php

declare(strict_types=1);

namespace Fixtures;

use App\Auth\Entities\User;
use App\Auth\Repositories\DbUserRepository;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Core\Database\Fixtures\DbFixture;
use App\Core\Tokens\Services\JsonWebTokenizerService;
use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;

final class UserFixture extends DbFixture
{
    private const ID = Uuid::NIL;
    private const HASH = '$argon2i$v=19$m=32,t=4,p=1$cGdVR1FhaWZaZ3dYWXJiRA$cwVrKnoFkGvdENiWPEsyzH03kUXz1F43lOEfIa4SCfM';

    public function fixture(EntityManagerInterface $manager): void
    {
        $users = new DbUserRepository($manager);
        $webTokens = new JsonWebTokenizerService(new Key((string)env('JWT_KEY'), env('JWT_ALGO')));

        $date = new \DateTimeImmutable();
        $user = User::createByEmail(
            id: new Id(Uuid::NIL),
            email: new Email('admin@4records.com'),
            hash: new PasswordHash(self::HASH),
            token: Token::create($date->modify('+1 day')),
            createdAt: $date
        );
        $user->setTokenAccess($webTokens->encode(payload: ['user' => Uuid::NIL], expire: '+30 months'));
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
