<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\SearchGames;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Page\SortFields;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Games $games,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        $filter = $query->q !== '' ? new Contains(new Field('name'), $query->q) : All::Filter;
        $sort = new PageSort(new SortFields(['name' => SortDirection::Asc]));

        $keys = $this->games->search($filter, new PageSize($query->size), new PageNumber($query->page), $sort);
        $total = $this->games->count($filter);

        $data = [];
        foreach ($keys as $key) {
            /** @var mixed $rawId */
            $rawId = $key['id'] ?? null;
            if ($rawId === null) {
                continue;
            }

            /** @var Game|null $game */
            $game = $this->games->find((string)$rawId);
            if ($game !== null) {
                $data[] = [
                    'id' => (string)$game->getId(),
                    'bggId' => $game->getBggId(),
                    'name' => $game->getName(),
                    'yearPublished' => $game->getYearPublished(),
                ];
            }
        }

        return new Result(
            data: $data,
            total: $total,
            page: $query->page,
            size: $query->size,
        );
    }
}
