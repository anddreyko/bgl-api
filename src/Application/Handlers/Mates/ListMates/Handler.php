<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\ListMates;

use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Mate;
use Bgl\Domain\Mates\Mates;

/**
 * @implements MessageHandler<Result, Query>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Mates $mates,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Query $query */
        $query = $envelope->message;

        $userId = new Uuid($query->userId);
        $offset = ($query->page - 1) * $query->size;

        $mates = $this->mates->findAllByUser($userId, $query->size, $offset, $query->sort, $query->order);
        $systemMates = $this->mates->findSystemMates();
        $total = $this->mates->countByUser($userId);

        $data = array_map($this->transformMate(...), $systemMates);
        foreach ($mates as $mate) {
            $data[] = $this->transformMate($mate);
        }

        return new Result(data: $data, total: $total + \count($systemMates), page: $query->page, size: $query->size);
    }

    /**
     * @return array{id: string, name: string, notes: ?string, is_system: bool, createdAt: string}
     */
    private function transformMate(Mate $mate): array
    {
        return [
            'id' => (string)$mate->getId(),
            'name' => $mate->getName(),
            'notes' => $mate->getNotes(),
            'is_system' => $mate->isSystem(),
            'createdAt' => $mate->getCreatedAt()->getFormattedValue('c'),
        ];
    }
}
