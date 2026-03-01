<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark\Core;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Password;
use Bgl\Core\ValueObjects\Uuid;
use PhpBench\Attributes as Bench;

final class ValueObjectBench
{
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchUuidCreate(): void
    {
        new Uuid('550e8400-e29b-41d4-a716-446655440000');
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchDateTimeCreate(): void
    {
        new DateTime();
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchDateTimeFromString(): void
    {
        new DateTime('2024-01-01 00:00:00');
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchEmailCreate(): void
    {
        new Email('bench@example.com');
    }

    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Assert('mode(variant.time.avg) <= mode(baseline.time.avg) +/- 15%')]
    public function benchPasswordCreate(): void
    {
        new Password('BenchPass123!');
    }
}
