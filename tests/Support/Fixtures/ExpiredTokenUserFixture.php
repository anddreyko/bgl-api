<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
use App\Auth\Repositories\DbUserRepository as UserRepository;
use App\Auth\ValueObjects\Email;
use App\Auth\ValueObjects\Id;
use App\Auth\ValueObjects\PasswordHash;
use App\Auth\ValueObjects\Token;
use App\Auth\ValueObjects\WebToken;
use App\Core\Database\Fixtures\DbFixture;
use App\Core\Tokens\Services\JsonWebTokenizerService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\Key;

final class ExpiredTokenUserFixture extends DbFixture
{
    public const UUID = '22222222-2222-2222-2222-222222222222';
    public const EMAIL = 'existed-user@app.test';
    public const PASS = 'password';
    public const HASH = '$argon2i$v=19$m=65536,t=4,p=1$aWpLR3FaMFVYZGlUODJXWg$8gmO8pfngi1MxYWCcMSMuf/yyI/mrIlvPevYyjUHkG4';

    public static WebToken $token;

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

        self::$token = (new JsonWebTokenizerService(new Key(getenv('JWT_KEY'), 'HS512')))
            ->encode(payload: ['user' => self::UUID], expire: '-30 minutes');
        $user->setTokenAccess(self::$token);

        $user->setStatus(UserStatusEnum::Active);

        $users->add($user);

        $manager->flush();
    }
}
