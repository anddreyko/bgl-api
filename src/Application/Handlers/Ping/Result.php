<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Ping;

use Bgl\Core\ValueObjects\DateInterval;
use Bgl\Core\ValueObjects\DateTime;

final readonly class Result
{
    public function __construct(
        public DateTime $datetime,
        public DateInterval $delay,
        public string $version,
        public string $environment,
        public string $messageId,
        public ?string $parentId,
        public ?string $traceId,
    ) {
    }
}
