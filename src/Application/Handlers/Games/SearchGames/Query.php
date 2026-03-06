<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\SearchGames;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    public function __construct(
        public string $q = '',
        public int $page = 1,
        public int $size = 20,
    ) {
    }
}
