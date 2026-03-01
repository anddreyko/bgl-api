# Stage 1: Domain + Infrastructure (PLAYS-002)

## Goal

No domain/infrastructure changes needed. Plays already extends Repository + Searchable.
DoctrineRepository base provides search() and count() with Filter support.

## Existing Infrastructure

- `Plays extends Repository, Searchable` -- search(filter, size, number, sort) + count(filter)
- `DoctrineRepository::search()` -- QueryBuilder with DoctrineFilter visitor
- `DoctrineFilter` -- supports Equals, Greater, Less, Contains, AndX, OrX
- `PageSize`, `PageNumber`, `PageSort`, `SortDirection` -- pagination primitives

## What Handler Will Use

```php
$filters = [];
$filters[] = new Equals(new Field('userId'), new Uuid($query->userId));

if ($query->gameId !== null) {
    $filters[] = new Equals(new Field('gameId'), new Uuid($query->gameId));
}
if ($query->from !== null) {
    $filters[] = new Greater(new Field('startedAt'), new DateTimeImmutable($query->from));
}
if ($query->to !== null) {
    $filters[] = new Less(new Field('startedAt'), new DateTimeImmutable($query->to));
}

$filter = count($filters) > 1 ? new AndX($filters) : $filters[0];

$keys = $this->plays->search($filter, new PageSize($query->size), new PageNumber($query->page), $sort);
$total = $this->plays->count($filter);
```

## Validation

No changes needed -- skip to Stage 2.
