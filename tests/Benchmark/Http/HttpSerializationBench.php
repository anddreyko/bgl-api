<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Http;

use Bgl\Application\Handlers\Games\SearchGames\Query;
use Bgl\Application\Handlers\Games\SearchGames\Result;
use Bgl\Core\Serialization\Serializer;
use Bgl\Tests\Benchmark\BenchHelper;
use EventSauce\ObjectHydrator\ObjectMapper;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
#[Bench\Assert('mode(variant.time.avg) <= mode(baseline.time.avg) +/- 15%')]
final class HttpSerializationBench
{
    private Serializer $serializer;
    private ObjectMapper $hydrator;

    public function setUp(): void
    {
        $this->serializer = BenchHelper::get(Serializer::class);
        $this->hydrator = BenchHelper::get(ObjectMapper::class);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchSerializeResult(): void
    {
        $result = new Result(
            data: [['id' => 'uuid', 'bggId' => 13, 'name' => 'Catan', 'yearPublished' => 1995]],
            total: 1,
            page: 1,
            size: 20,
        );

        $this->serializer->serialize($result);
    }

    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchHydrateMessage(): void
    {
        $this->hydrator->hydrateObject(Query::class, ['q' => 'Catan', 'page' => 1, 'size' => 20]);
    }
}
