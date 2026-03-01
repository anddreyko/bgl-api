<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Handlers;

use Bgl\Application\Handlers\Auth\ConfirmEmail;
use Bgl\Application\Handlers\Auth\Register;
use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Tests\Benchmark\BenchHelper;
use Bgl\Tests\Support\Dummy\FakeConfirmer;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class AuthBench
{
    private Register\Handler $registerHandler;
    private ConfirmEmail\Handler $confirmHandler;
    private Users $users;
    private FakeConfirmer $confirmer;
    private int $counter = 0;

    public function setUp(): void
    {
        $this->registerHandler = BenchHelper::get(Register\Handler::class);
        $this->confirmHandler = BenchHelper::get(ConfirmEmail\Handler::class);
        $this->users = BenchHelper::get(Users::class);

        /** @var FakeConfirmer $confirmer */
        $confirmer = BenchHelper::get(Confirmer::class);
        $this->confirmer = $confirmer;

        $this->counter = 0;
        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Assert('mode(variant.time.avg) <= mode(baseline.time.avg) +/- 15%')]
    public function benchRegister(): void
    {
        ++$this->counter;
        ($this->registerHandler)(new Envelope(
            message: new Register\Command(
                email: "bench{$this->counter}@example.com",
                password: 'BenchPass123!',
                name: 'Bench User',
            ),
            messageId: "bench-register-{$this->counter}",
        ));
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchConfirmEmail(): void
    {
        $user = User::register(
            id: new Uuid('confirm-user-id'),
            email: new Email('confirm@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('now'),
            name: 'Test',
        );
        $this->users->add($user);
        $this->confirmer->request($user->getId());

        /** @var string $token */
        $token = $this->confirmer->getLastToken();

        ($this->confirmHandler)(new Envelope(
            message: new ConfirmEmail\Command(token: $token),
            messageId: 'bench-confirm',
        ));

        BenchHelper::clearRepositories();
    }
}
