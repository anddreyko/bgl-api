<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Handlers;

use Bgl\Application\Handlers\User\GetUser;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class UserBench
{
    private GetUser\Handler $getHandler;
    private Users $users;

    public function setUp(): void
    {
        $this->getHandler = BenchHelper::get(GetUser\Handler::class);
        $this->users = BenchHelper::get(Users::class);

        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchGetUser(): void
    {
        $user = User::register(
            id: new Uuid('bench-user-id'),
            email: new Email('bench@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('now'),
            name: 'Bench User',
        );
        $user->confirm();
        $this->users->add($user);

        ($this->getHandler)(new Envelope(
            message: new GetUser\Query(userId: 'bench-user-id'),
            messageId: 'bench-get-user',
        ));
    }
}
