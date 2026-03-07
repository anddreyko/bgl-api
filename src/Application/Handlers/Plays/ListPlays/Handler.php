<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\ListPlays;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Domain\Plays\PlayStatus;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Page\SortFields;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Profile\Users;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\Plays;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Games $games,
        private Users $users,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        $filter = $this->buildFilter($query);
        $sort = new PageSort(new SortFields(['startedAt' => SortDirection::Desc]));

        $keys = $this->plays->search($filter, new PageSize($query->size), new PageNumber($query->page), $sort);
        $total = $this->plays->count($filter);

        $data = [];
        foreach ($keys as $key) {
            /** @var mixed $rawId */
            $rawId = $key['id'] ?? null;
            if ($rawId === null) {
                continue;
            }

            /** @var Play|null $play */
            $play = $this->plays->find((string)$rawId);
            if ($play === null) {
                continue;
            }

            $data[] = $this->transformPlay($play);
        }

        return new Result(
            data: $data,
            total: $total,
            page: $query->page,
            size: $query->size,
        );
    }

    private function buildFilter(Query $query): Filter
    {
        $userFilter = new Equals(new Field('userId'), $query->userId);

        /** @var list<Filter> $extra */
        $extra = [
            new Not(new Equals(new Field('status'), PlayStatus::Deleted->value)),
        ];

        if ($query->gameId !== null && $query->gameId !== '') {
            $extra[] = new Equals(new Field('gameId'), $query->gameId);
        }

        if ($query->from !== null) {
            $extra[] = new Greater(new Field('startedAt'), new DateTime($query->from));
        }

        if ($query->to !== null) {
            $extra[] = new Less(new Field('startedAt'), new DateTime($query->to));
        }

        return new AndX([$userFilter, ...$extra]);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array{id: string, name: string}
     */
    private function resolveAuthor(Play $play): array
    {
        $author = ['id' => (string)$play->getUserId(), 'name' => ''];
        $user = $this->users->find((string)$play->getUserId());
        if ($user !== null) {
            $author['name'] = $user->getName();
        }

        return $author;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPlay(Play $play): array
    {
        $game = null;
        $gameId = $play->getGameId();
        if ($gameId !== null) {
            /** @var Game|null $gameEntity */
            $gameEntity = $this->games->find((string)$gameId);
            if ($gameEntity !== null) {
                $game = [
                    'id' => (string)$gameEntity->getId(),
                    'name' => $gameEntity->getName(),
                ];
            }
        }

        $players = [];
        /** @var Player $player */
        foreach ($play->getPlayers() as $player) {
            $players[] = [
                'id' => (string)$player->getId(),
                'mate_id' => (string)$player->getMateId(),
                'score' => $player->getScore(),
                'is_winner' => $player->isWinner(),
                'color' => $player->getColor(),
            ];
        }

        return [
            'id' => (string)$play->getId(),
            'author' => $this->resolveAuthor($play),
            'name' => $play->getName(),
            'status' => $play->getStatus()->value,
            'visibility' => $play->getVisibility()->value,
            'started_at' => $play->getStartedAt()->getNullableFormattedValue('c'),
            'finished_at' => $play->getFinishedAt()?->getNullableFormattedValue('c'),
            'game' => $game,
            'players' => $players,
        ];
    }
}
