<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Modules;

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Persistence\Transactor;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\Passkeys;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryGames;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryMates;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPasskeyChallenges;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPasskeys;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlays;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryRepository;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryUsers;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Dummy\FakeConfirmer;
use Bgl\Tests\Support\Dummy\NullTransactor;
use Codeception\Module;
use DI\Container;

final class FunctionalPersistenceModule extends Module
{
    #[\Override]
    public function _beforeSuite(array $settings = []): void
    {
        $container = DiHelper::container();
        \assert($container instanceof Container);

        $container->set(Users::class, new InMemoryUsers());
        $container->set(Plays::class, new InMemoryPlays());
        $container->set(Mates::class, new InMemoryMates());
        $container->set(Games::class, new InMemoryGames());
        $container->set(Passkeys::class, new InMemoryPasskeys());
        $container->set(PasskeyChallenges::class, new InMemoryPasskeyChallenges());
        $container->set(Transactor::class, new NullTransactor());
        $container->set(Confirmer::class, new FakeConfirmer());
    }

    #[\Override]
    public function _before(\Codeception\TestInterface $test): void
    {
        $container = DiHelper::container();

        foreach ([Users::class, Plays::class, Mates::class, Games::class, Passkeys::class, PasskeyChallenges::class] as $repoInterface) {
            $repo = $container->get($repoInterface);
            if ($repo instanceof InMemoryRepository) {
                $repo->clear();
            }
        }
    }

    #[\Override]
    public function _afterSuite(): void
    {
        DiHelper::reset();
    }
}
