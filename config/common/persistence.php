<?php

declare(strict_types=1);

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Persistence\Transactor;
use Bgl\Core\Serialization\Deserializer;
use Bgl\Domain\Games\Entities\Games;
use Bgl\Domain\Mates\Entities\Mates;
use Bgl\Domain\Plays\Entities\PlayersFactory;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\Passkeys;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Infrastructure\Auth\DoctrineConfirmer;
use Bgl\Infrastructure\Clients\Bgg\XmlFieldExtractor;
use Bgl\Infrastructure\Identity\RamseyUuidGenerator;
use Bgl\Infrastructure\Persistence\Bgg\BggGames;
use Bgl\Infrastructure\Persistence\CompositeGames;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactor;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Games\Games as DoctrineGames;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Mates\Mates as DoctrineMates;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\DoctrinePlayersFactory;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Plays\Plays as DoctrinePlays;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\PasskeyChallenges as DoctrineChallenges;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\Passkeys as DoctrinePasskeys;
use Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile\Users as DoctrineUsers;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;

return [
    UuidGenerator::class => static fn(RamseyUuidGenerator $g): UuidGenerator => $g,
    Transactor::class => static fn(DoctrineTransactor $t): Transactor => $t,
    Users::class => static fn(EntityManagerInterface $em): Users => new DoctrineUsers($em),
    Confirmer::class => static fn(DoctrineConfirmer $c): Confirmer => $c,
    Plays::class => static fn(EntityManagerInterface $em): Plays => new DoctrinePlays($em),
    PlayersFactory::class => static fn(DoctrinePlayersFactory $f): PlayersFactory => $f,
    Passkeys::class => static fn(EntityManagerInterface $em): Passkeys => new DoctrinePasskeys($em),
    PasskeyChallenges::class => static fn(EntityManagerInterface $em): PasskeyChallenges => new DoctrineChallenges($em),
    Mates::class => static fn(EntityManagerInterface $em): Mates => new DoctrineMates($em),
    Games::class => static function (ContainerInterface $c): Games {
        /** @var array{base_url: string, search: array{endpoint: string, params: array<string, string>, timeout: int, mapping: array<string, string>, required: list<string>}} $bgg */
        $bgg = $c->get('bgg');

        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = $c->get(UuidGenerator::class);
        /** @var \Psr\Clock\ClockInterface $clock */
        $clock = $c->get(ClockInterface::class);
        /** @var EntityManagerInterface $em */
        $em = $c->get(EntityManagerInterface::class);

        /** @var \Bgl\Core\Serialization\Deserializer $deserializer */
        $deserializer = $c->get(Deserializer::class);

        $remote = new BggGames(
            new Client(['base_uri' => $bgg['base_url']]),
            new XmlFieldExtractor(),
            $deserializer,
            $uuidGenerator,
            $clock,
            $bgg['search'],
        );

        return new CompositeGames(
            new DoctrineGames($em),
            $remote,
        );
    },
];
