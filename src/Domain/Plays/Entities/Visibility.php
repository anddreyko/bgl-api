<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays\Entities;

/**
 * Play session visibility levels.
 */
enum Visibility: string
{
    /** Only the play owner can see */
    case Private = 'private';

    /** Anyone with a direct link can see */
    case Link = 'link';

    /** Owner's mates (co-players) can see */
    case Friends = 'friends';

    /** All registered users can see */
    case Registered = 'registered';

    /** Visible to everyone */
    case Public = 'public';
}
