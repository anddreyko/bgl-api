<?php

declare(strict_types=1);

namespace Bgl\Core\Messages;

/**
 * @template TResult of void = mixed
 * @extends Message<TResult>
 */
interface Command extends Message
{
}
