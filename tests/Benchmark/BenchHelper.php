<?php

declare(strict_types=1);

namespace Bgl\Tests\Benchmark;

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Persistence\Transactor;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Domain\Plays\Entities\Players;
use Bgl\Domain\Plays\Entities\PlayersFactory;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\Passkeys;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryGames;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryMates;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPasskeyChallenges;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPasskeys;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayers;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlayersFactory;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryPlays;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryRepository;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryUsers;
use Bgl\Tests\Support\Dummy\FakeConfirmer;
use Bgl\Tests\Support\Dummy\NullTransactor;
use DI\Container;
use Psr\Container\ContainerInterface;

final class BenchHelper
{
    private static ?ContainerInterface $container = null;

    public static function container(): ContainerInterface
    {
        if (self::$container === null) {
            self::$container = require __DIR__ . '/../../config/container.php';
            self::overrideWithInMemory();
        }

        return self::$container;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function get(string $class): object
    {
        /** @var T */
        return self::container()->get($class);
    }

    public static function clearRepositories(): void
    {
        $repos = [
            Users::class,
            Plays::class,
            Players::class,
            Mates::class,
            Games::class,
            Passkeys::class,
            PasskeyChallenges::class,
        ];

        foreach ($repos as $repoInterface) {
            $repo = self::container()->get($repoInterface);
            if ($repo instanceof InMemoryRepository) {
                $repo->clear();
            }
        }
    }

    public static function reset(): void
    {
        self::$container = null;
    }

    private static function overrideWithInMemory(): void
    {
        $container = self::$container;
        \assert($container instanceof Container);

        $container->set(Users::class, new InMemoryUsers());
        $container->set(Plays::class, new InMemoryPlays());
        $container->set(Players::class, new InMemoryPlayers());
        $container->set(PlayersFactory::class, new InMemoryPlayersFactory());
        $container->set(Mates::class, new InMemoryMates());
        $container->set(Games::class, new InMemoryGames());
        $container->set(Passkeys::class, new InMemoryPasskeys());
        $container->set(PasskeyChallenges::class, new InMemoryPasskeyChallenges());
        $container->set(Transactor::class, new NullTransactor());
        $container->set(Confirmer::class, new FakeConfirmer());
    }
}
