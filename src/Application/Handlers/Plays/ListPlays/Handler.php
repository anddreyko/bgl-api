<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\ListPlays;

use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\OrX;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Page\SortFields;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Domain\Games\Game;
use Bgl\Domain\Games\Games;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;
use Bgl\Domain\Plays\Play;
use Bgl\Domain\Plays\Player\Player;
use Bgl\Domain\Plays\PlayLifecycle;
use Bgl\Domain\Plays\Plays;
use Bgl\Domain\Plays\Visibility;
use Bgl\Domain\Profile\UserResolver;
use Bgl\Domain\Profile\Users;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Plays $plays,
        private Games $games,
        private Users $users,
        private Mates $mates,
        private Locations $locations,
        private UserResolver $userResolver,
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
        $targetUserId = $this->resolveTargetUserId($query);

        /** @var non-empty-list<Filter> $filters */
        $filters = [
            new Not(new Equals(new Field('lifecycle'), PlayLifecycle::Deleted->value)),
        ];

        if ($targetUserId !== null) {
            $filters[] = new Equals(new Field('userId'), $targetUserId);
        }

        $isViewingOwn = $targetUserId !== null && $targetUserId === $query->userId;
        if (!$isViewingOwn) {
            $filters[] = $this->visibilityFilter($query->userId !== null);
        }

        $this->addOptionalFilters($filters, $query);

        return new AndX($filters);
    }

    private function resolveTargetUserId(Query $query): ?string
    {
        $authorId = $query->authorId !== null
            ? ($this->userResolver->resolveId($query->authorId) ?? throw new NotFoundException('Author not found'))
            : null;

        return $authorId ?? $query->userId;
    }

    /**
     * @param non-empty-list<Filter> $filters
     */
    private function addOptionalFilters(array &$filters, Query $query): void
    {
        if ($query->gameId !== null && $query->gameId !== '') {
            $filters[] = new Equals(new Field('gameId'), $query->gameId);
        }

        if ($query->status !== null && $query->status !== '') {
            $filters[] = new Equals(new Field('lifecycle'), $query->status);
        }

        if ($query->from !== null) {
            $filters[] = new Greater(new Field('startedAt'), new DateTime($query->from));
        }

        if ($query->to !== null) {
            $filters[] = new Less(new Field('startedAt'), new DateTime($query->to));
        }
    }

    private function visibilityFilter(bool $isAuthenticated): OrX
    {
        $allowed = $isAuthenticated
            ? [Visibility::Public, Visibility::Link, Visibility::Authenticated]
            : [Visibility::Public, Visibility::Link];

        return new OrX(
            array_map(
                static fn(Visibility $v): Equals => new Equals(new Field('visibility'), $v->value),
                $allowed,
            )
        );
    }

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
     * @return ?array{id: string, name: string}
     */
    private function resolveGame(Play $play): ?array
    {
        $gameId = $play->getGameId();
        if ($gameId === null) {
            return null;
        }

        /** @var Game|null $gameEntity */
        $gameEntity = $this->games->find((string)$gameId);

        return $gameEntity !== null
            ? ['id' => (string)$gameEntity->getId(), 'name' => $gameEntity->getName()]
            : null;
    }

    /**
     * @return list<array{
     *     id: string,
     *     mate: array{id: string, name: string},
     *     score: ?int,
     *     is_winner: bool,
     *     color: ?string,
     *     team_tag: ?string,
     *     number: ?int
     * }>
     */
    private function transformPlayers(Play $play): array
    {
        $players = [];
        /** @var Player $player */
        foreach ($play->getPlayers() as $player) {
            $mateId = (string)$player->getMateId();
            /** @var Mate|null $mate */
            $mate = $this->mates->find($mateId);

            $players[] = [
                'id' => (string)$player->getId(),
                'mate' => ['id' => $mateId, 'name' => $mate !== null ? $mate->getName() : ''],
                'score' => $player->getScore(),
                'is_winner' => $player->isWinner(),
                'color' => $player->getColor(),
                'team_tag' => $player->getTeamTag(),
                'number' => $player->getNumber(),
            ];
        }

        return $players;
    }

    /**
     * @return ?array{id: string, name: string}
     */
    private function resolveLocation(Play $play): ?array
    {
        $locationId = $play->getLocationId();
        if ($locationId === null) {
            return null;
        }

        /** @var Location|null $location */
        $location = $this->locations->find((string)$locationId);

        return $location !== null
            ? ['id' => (string)$location->getId(), 'name' => $location->getName()]
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPlay(Play $play): array
    {
        return [
            'id' => (string)$play->getId(),
            'author' => $this->resolveAuthor($play),
            'name' => $play->getName(),
            'visibility' => $play->getVisibility()->value,
            'started_at' => $play->getStartedAt()->getNullableFormattedValue('c'),
            'finished_at' => $play->getFinishedAt()?->getNullableFormattedValue('c'),
            'game' => $this->resolveGame($play),
            'players' => $this->transformPlayers($play),
            'status' => $play->getLifecycle()->value,
            'notes' => $play->getNotes(),
            'location' => $this->resolveLocation($play),
        ];
    }
}
