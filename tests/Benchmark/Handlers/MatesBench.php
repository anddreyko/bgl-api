<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Handlers;

use Bgl\Application\Handlers\Mates\CreateMate;
use Bgl\Application\Handlers\Mates\DeleteMate;
use Bgl\Application\Handlers\Mates\GetMate;
use Bgl\Application\Handlers\Mates\ListMates;
use Bgl\Application\Handlers\Mates\UpdateMate;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Tests\Benchmark\BenchHelper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\AfterMethods('tearDown')]
final class MatesBench
{
    private const string USER_ID = 'bench-mates-user';

    private CreateMate\Handler $createHandler;
    private GetMate\Handler $getHandler;
    private ListMates\Handler $listHandler;
    private UpdateMate\Handler $updateHandler;
    private DeleteMate\Handler $deleteHandler;
    private Mates $mates;
    private int $counter = 0;

    public function setUp(): void
    {
        $this->createHandler = BenchHelper::get(CreateMate\Handler::class);
        $this->getHandler = BenchHelper::get(GetMate\Handler::class);
        $this->listHandler = BenchHelper::get(ListMates\Handler::class);
        $this->updateHandler = BenchHelper::get(UpdateMate\Handler::class);
        $this->deleteHandler = BenchHelper::get(DeleteMate\Handler::class);
        $this->mates = BenchHelper::get(Mates::class);

        $this->counter = 0;
        BenchHelper::clearRepositories();
    }

    public function tearDown(): void
    {
        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchCreateMate(): void
    {
        ++$this->counter;
        ($this->createHandler)(new Envelope(
            message: new CreateMate\Command(
                userId: self::USER_ID,
                name: "Mate {$this->counter}",
                notes: 'Bench notes',
            ),
            messageId: "bench-create-mate-{$this->counter}",
        ));
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchGetMate(): void
    {
        $mate = $this->seedMate('get-mate-id');

        ($this->getHandler)(new Envelope(
            message: new GetMate\Query(
                userId: self::USER_ID,
                mateId: (string)$mate->getId(),
            ),
            messageId: 'bench-get-mate',
        ));
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchListMates(): void
    {
        for ($i = 0; $i < 20; ++$i) {
            $this->seedMate("list-mate-{$i}");
        }

        ($this->listHandler)(new Envelope(
            message: new ListMates\Query(userId: self::USER_ID),
            messageId: 'bench-list-mates',
        ));

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchUpdateMate(): void
    {
        $mate = $this->seedMate('update-mate-id');

        ($this->updateHandler)(new Envelope(
            message: new UpdateMate\Command(
                userId: self::USER_ID,
                mateId: (string)$mate->getId(),
                name: 'Updated Name',
                notes: 'Updated notes',
            ),
            messageId: 'bench-update-mate',
        ));

        BenchHelper::clearRepositories();
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchDeleteMate(): void
    {
        $mate = $this->seedMate('delete-mate-id');

        ($this->deleteHandler)(new Envelope(
            message: new DeleteMate\Command(
                userId: self::USER_ID,
                mateId: (string)$mate->getId(),
            ),
            messageId: 'bench-delete-mate',
        ));

        BenchHelper::clearRepositories();
    }

    private function seedMate(string $id): Mate
    {
        $mate = Mate::create(
            new Uuid($id),
            new Uuid(self::USER_ID),
            "Mate {$id}",
            null,
            new DateTime('now'),
        );
        $this->mates->add($mate);

        return $mate;
    }
}
