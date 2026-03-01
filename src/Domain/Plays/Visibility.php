<?php

declare(strict_types=1);

namespace Bgl\Domain\Plays;

/**
 * Play session visibility levels.
 */
enum Visibility: string
{
    /** Only the play owner can see */
    case Private = 'private';

    /** Anyone with a direct link can see */
    case Link = 'link';

    /** Author + Users linked to Mates in this Play */
    case Participants = 'participants';

    /** All authenticated users can see */
    case Authenticated = 'authenticated';

    /** Visible to everyone */
    case Public = 'public';
}
