<?php

declare(strict_types=1);

namespace App\Plays\Repositories;

use App\Core\Database\Repositories\DbRepository;
use App\Core\Exceptions\NotFoundException;
use App\Core\ValueObjects\Id;
use App\Plays\Entities\Session;

final class SessionRepository extends DbRepository
{
    public function getClass(): string
    {
        return Session::class;
    }

    public function create(Session $session): void
    {
        $this->persist($session);
    }

    public function getOneById(Id $id): Session
    {
        $session = $this->findOneBy(['id' => $id->getValue()]);
        if ($session instanceof Session === false) {
            throw new NotFoundException();
        }

        return $session;
    }
}
