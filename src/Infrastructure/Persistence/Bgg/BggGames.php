<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Bgg;

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Serialization\Denormalizer;
use Bgl\Core\Serialization\Deserializer;
use Bgl\Core\Serialization\FieldMapping;
use Bgl\Core\Serialization\RequiredFields;
use Bgl\Core\Serialization\SerializedData;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Infrastructure\Persistence\InMemory\InMemoryGames;
use GuzzleHttp\ClientInterface;
use Psr\Clock\ClockInterface;

final readonly class BggGames implements Games
{
    private InMemoryGames $cache;

    /**
     * @param array{
     *     endpoint: string,
     *     params: array<string, string>,
     *     timeout: int,
     *     mapping: array<string, string>,
     *     required: list<string>,
     * } $searchConfig
     */
    public function __construct(
        private ClientInterface $client,
        private Denormalizer $denormalizer,
        private Deserializer $deserializer,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
        private array $searchConfig,
    ) {
        $this->cache = new InMemoryGames();
    }

    #[\Override]
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort(),
    ): iterable {
        $query = $this->extractSearchQuery($filter);
        if ($query === null) {
            return [];
        }

        foreach ($this->fetchFromBgg($query) as $game) {
            $this->cache->add($game);
        }

        return $this->cache->search($filter, $size, $number, $sort);
    }

    #[\Override]
    public function count(Filter $filter = All::Filter): int
    {
        return $this->cache->count($filter);
    }

    #[\Override]
    public function find(string $id): ?Game
    {
        return $this->cache->find($id);
    }

    #[\Override]
    public function findByBggId(int $bggId): ?Game
    {
        return null;
    }

    #[\Override]
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = [];
        foreach ($ids as $id) {
            $game = $this->cache->find($id);
            if ($game !== null) {
                $result[] = $game;
            }
        }

        return $result;
    }

    #[\Override]
    public function add(object $entity): void
    {
        throw new \LogicException('BggGames is read-only');
    }

    #[\Override]
    public function remove(object $entity): void
    {
        throw new \LogicException('BggGames is read-only');
    }

    /**
     * @return list<Game>
     */
    private function fetchFromBgg(string $query): array
    {
        $params = array_merge($this->searchConfig['params'], ['query' => $query]);
        $response = $this->client->request('GET', $this->searchConfig['endpoint'], [
            'query' => $params,
            'timeout' => $this->searchConfig['timeout'],
        ]);

        try {
            $xml = new \SimpleXMLElement((string)$response->getBody());
        } catch (\Exception) {
            return [];
        }

        $now = new DateTime($this->clock->now());
        $games = [];
        foreach ($xml as $item) {
            $denormalized = $this->denormalizer->denormalize(
                $item,
                FieldMapping::fromArray($this->searchConfig['mapping']),
                RequiredFields::fromArray($this->searchConfig['required']),
            );
            if ($denormalized === null) {
                continue;
            }

            $enrichedData = array_merge(
                $denormalized->toArray(),
                ['id' => $this->uuidGenerator->generate(), 'createdAt' => $now],
            );

            /** @var Game $game */
            $game = $this->deserializer->deserialize(SerializedData::fromArray($enrichedData), Game::class);
            $games[] = $game;
        }

        return $games;
    }

    private function extractSearchQuery(Filter $filter): ?string
    {
        if ($filter instanceof Contains && $filter->left instanceof Field) {
            return (string)$filter->right;
        }

        return null;
    }
}
