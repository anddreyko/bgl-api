<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Core\ValueObjects\Email;
use App\Core\ValueObjects\Id;
use App\Core\ValueObjects\PasswordHash;
use App\Core\ValueObjects\Token;
use App\Core\ValueObjects\WebToken;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Enums\UserStatusEnum;
use App\Domain\Auth\Repositories\DbUserRepository as UserRepository;
use App\Infrastructure\Database\Fixtures\DbFixture;
use App\Infrastructure\Tokens\JsonWebTokenizer;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\Key;

final class TwoSessionsUserFixture extends DbFixture
{
    public const UUID = '22222222-2222-2222-2222-222222222222';
    public const EMAIL = 'existed-user@app.test';
    public const PASS = 'password';
    public const HASH = '$argon2i$v=19$m=65536,t=4,p=1$aWpLR3FaMFVYZGlUODJXWg$8gmO8pfngi1MxYWCcMSMuf/yyI/mrIlvPevYyjUHkG4';

    public static WebToken $token1;
    public static WebToken $token2;

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

        self::$token1 = (new JsonWebTokenizer(new Key(env('JWT_KEY'), env('JWT_ALGO'))))
            ->encode(payload: ['user' => self::UUID], issuedAt: $date);
        $user->setTokenAccess(self::$token1);

        self::$token2 = (new JsonWebTokenizer(new Key(env('JWT_KEY'), env('JWT_ALGO'))))
            ->encode(payload: ['user' => self::UUID], expire: '+29 minutes', issuedAt: $date);
        $user->setTokenAccess(self::$token2);

        $user->setStatus(UserStatusEnum::Active);

        $users->add($user);

        $manager->flush();
    }
}
