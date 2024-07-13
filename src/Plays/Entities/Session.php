<?php

declare(strict_types=1);

namespace App\Plays\Entities;

use App\Core\ValueObjects\Id;
use App\Plays\Enums\SessionStatus;
use App\Plays\Types\IdType;
use App\Plays\Types\SessionStatusType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'records_session')]
final class Session
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: IdType::NAME)]
        private Id $id,
        #[ORM\Column(type: Types::STRING, options: ["default" => ''])]
        private string $name,
        #[ORM\Column(type: SessionStatusType::NAME)]
        private SessionStatus $status = SessionStatus::Draft,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => 'CURRENT_TIMESTAMP'])]
        private \DateTimeImmutable $startedAt = new \DateTimeImmutable(),
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ["default" => null])]
        private ?\DateTimeImmutable $finishedAt = null,
    ) {
    }


    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTimeImmutable $startedAt
     *
     * @return Session
     */
    public function setStartedAt(\DateTimeImmutable $startedAt): Session
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTimeImmutable $finishedAt
     *
     * @return Session
     */
    public function setFinishedAt(\DateTimeImmutable $finishedAt): Session
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }

    /**
     * @param Id $id
     *
     * @return Session
     */
    public function setId(Id $id): Session
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Session
     */
    public function setName(string $name): Session
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return SessionStatus
     */
    public function getStatus(): SessionStatus
    {
        return $this->status;
    }

    /**
     * @param SessionStatus $status
     *
     * @return Session
     */
    public function setStatus(SessionStatus $status): Session
    {
        $this->status = $status;

        return $this;
    }
}
