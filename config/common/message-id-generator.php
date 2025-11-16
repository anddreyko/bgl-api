<?php

declare(strict_types=1);

use Bgl\Core\Messages\MessageIdGenerator;
use Bgl\Infrastructure\MessageId\RandomMessageIdGenerator;

return [
    MessageIdGenerator::class => static fn(): MessageIdGenerator => new RandomMessageIdGenerator(
        (string)getenv('APP_ENV')
    ),
];
