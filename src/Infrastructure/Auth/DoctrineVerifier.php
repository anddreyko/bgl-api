<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\Credentials;
use Bgl\Core\Auth\CredentialType;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Auth\TooManyAttemptsException;
use Bgl\Core\Auth\TooManyRequestsException;
use Bgl\Core\Auth\Verifier;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

final readonly class DoctrineVerifier implements Verifier
{
    private const int CODE_LENGTH = 6;
    private const int TOKEN_TTL_MINUTES = 15;
    private const int MAX_ISSUES_PER_WINDOW = 3;
    private const int RATE_WINDOW_MINUTES = 10;
    private const int MAX_ATTEMPTS = 5;

    public function __construct(
        private EntityManagerInterface $em,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
        private string $pepper,
    ) {
    }

    #[\Override]
    public function canIssue(Uuid $userId): bool
    {
        return $this->countRecentTokens($userId) < self::MAX_ISSUES_PER_WINDOW;
    }

    #[\Override]
    public function issue(Uuid $userId): Credentials
    {
        if (!$this->canIssue($userId)) {
            throw new TooManyRequestsException();
        }

        $this->deleteOldTokens($userId);

        $code = $this->generateCode();
        $tokenValue = $this->uuidGenerator->generate()->getValue() ?? '';
        $now = new DateTime($this->clock->now());

        $token = VerificationToken::create(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            codeHash: $this->hashCode($code),
            token: $tokenValue,
            expiresAt: new DateTime('+' . self::TOKEN_TTL_MINUTES . ' minutes'),
            createdAt: $now,
        );

        $this->em->persist($token);

        /** @var non-empty-string $code */
        /** @var non-empty-string $tokenValue */
        return new Credentials($code, $tokenValue);
    }

    #[\Override]
    public function confirm(string $credential, CredentialType $type): Uuid
    {
        return match ($type) {
            CredentialType::Token => $this->confirmByToken($credential),
            CredentialType::Code => $this->confirmByCode($credential),
        };
    }

    private function confirmByToken(string $credential): Uuid
    {
        $entity = $this->findByToken($credential);
        if ($entity === null) {
            throw new InvalidConfirmationTokenException();
        }

        if ($entity->isExpired(new DateTime($this->clock->now()))) {
            $this->em->remove($entity);
            throw new ExpiredConfirmationTokenException();
        }

        $userId = $entity->getUserId();
        $this->em->remove($entity);

        return $userId;
    }

    private function confirmByCode(string $credential): Uuid
    {
        $hash = $this->hashCode($credential);
        $entity = $this->findByCodeHash($hash);

        if ($entity === null) {
            throw new InvalidConfirmationTokenException();
        }

        if ($entity->isExpired(new DateTime($this->clock->now()))) {
            $this->em->remove($entity);
            throw new ExpiredConfirmationTokenException();
        }

        if ($entity->getAttemptCount() >= self::MAX_ATTEMPTS) {
            throw new TooManyAttemptsException();
        }

        $userId = $entity->getUserId();
        $this->em->remove($entity);

        return $userId;
    }

    /**
     * @return non-empty-string
     */
    private function generateCode(): string
    {
        /** @var non-empty-string */
        return (string)random_int(100000, 999999);
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, $this->pepper);
    }

    private function deleteOldTokens(Uuid $userId): void
    {
        $this->em->createQueryBuilder()
            ->delete(VerificationToken::class, 't')
            ->where('t.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    private function countRecentTokens(Uuid $userId): int
    {
        $since = new \DateTimeImmutable('-' . self::RATE_WINDOW_MINUTES . ' minutes');

        /** @var int */
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(VerificationToken::class, 't')
            ->where('t.userId = :userId')
            ->andWhere('t.createdAt > :since')
            ->setParameter('userId', $userId)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function findByToken(string $token): ?VerificationToken
    {
        /** @var VerificationToken|null */
        return $this->em->createQueryBuilder()
            ->select('t')
            ->from(VerificationToken::class, 't')
            ->where('t.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function findByCodeHash(string $hash): ?VerificationToken
    {
        /** @var VerificationToken|null */
        return $this->em->createQueryBuilder()
            ->select('t')
            ->from(VerificationToken::class, 't')
            ->where('t.codeHash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
