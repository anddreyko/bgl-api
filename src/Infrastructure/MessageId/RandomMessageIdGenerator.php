<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\MessageId;

use Bgl\Core\Messages\MessageIdGenerator;

final readonly class RandomMessageIdGenerator implements MessageIdGenerator
{
    public function __construct(
        private string $prefix = '',
        private string $separate = '.',
        private int $min = 10000000,
        private int $max = 99999999
    ) {
    }

    #[\Override]
    public function generate(): string
    {
        $id = (string)random_int($this->min, $this->max);
        if ($this->prefix) {
            $id = $this->prefix . $this->separate . $id;
        }

        return $id;
    }
}
