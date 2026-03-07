<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Persistence\Transactor;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;

final readonly class CompositeGames implements Games
{
    public function __construct(
        private Games $local,
        private Games $remote,
        private Transactor $transactor,
    ) {
    }

    #[\Override]
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort(),
    ): iterable {
        $bggFailed = false;

        try {
            $remoteKeys = $this->remote->search($filter, $size, $number, $sort);
            foreach ($remoteKeys as $key) {
                $game = $this->remote->find((string)($key['id'] ?? ''));
                if ($game === null) {
                    continue;
                }

                $existing = $this->local->findByBggId($game->getBggId());
                if ($existing !== null) {
                    $existing->updateFromCatalog($game->getName(), $game->getYearPublished(), $game->getUpdatedAt());
                } else {
                    $this->local->add($game);
                }
            }
        } catch (\Throwable) {
            $bggFailed = true;
        }

        if (!$bggFailed) {
            $this->transactor->flush();
        }

        $results = $this->local->search($filter, $size, $number, $sort);

        if ($bggFailed && $results === [] && $this->local->count($filter) === 0) {
            throw new \RuntimeException('Service temporarily unavailable');
        }

        return $results;
    }

    #[\Override]
    public function count(Filter $filter = All::Filter): int
    {
        return $this->local->count($filter);
    }

    #[\Override]
    public function add(object $entity): void
    {
        $this->local->add($entity);
    }

    #[\Override]
    public function find(string $id): ?Game
    {
        return $this->local->find($id);
    }

    #[\Override]
    public function remove(object $entity): void
    {
        $this->local->remove($entity);
    }

    #[\Override]
    public function findByIds(array $ids): array
    {
        return $this->local->findByIds($ids);
    }

    #[\Override]
    public function findByBggId(int $bggId): ?Game
    {
        return $this->local->findByBggId($bggId);
    }
}
