<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Security;

use Bgl\Core\Security\TokenGenerator;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint;
use Psr\Clock\ClockInterface;

/**
 * @see \Bgl\Tests\Unit\Infrastructure\Security\JwtTokenGeneratorCest
 */
final readonly class JwtTokenGenerator implements TokenGenerator
{
    private Sha256 $signer;
    private InMemory $key;

    /**
     * @param non-empty-string $secret
     */
    public function __construct(
        string $secret,
        private ClockInterface $clock,
    ) {
        $this->signer = new Sha256();
        $this->key = InMemory::plainText($secret);
    }

    #[\Override]
    public function generate(array $payload, int $ttlSeconds): string
    {
        $facade = new JwtFacade(clock: $this->clock);

        $token = $facade->issue(
            $this->signer,
            $this->key,
            function (
                \Lcobucci\JWT\Builder $builder,
                \DateTimeImmutable $issuedAt,
            ) use (
                $payload,
                $ttlSeconds,
            ): \Lcobucci\JWT\Builder {
                $builder = $builder->expiresAt($issuedAt->modify('+' . $ttlSeconds . ' seconds'));

                /**
                 * @var non-empty-string $name
                 * @var mixed $value
                 */
                foreach ($payload as $name => $value) {
                    $builder = $builder->withClaim($name, $value);
                }

                return $builder;
            },
        );

        return $token->toString();
    }

    #[\Override]
    public function verify(string $token): array
    {
        if ($token === '') {
            throw new \RuntimeException('Invalid or expired token: token is empty');
        }

        try {
            $facade = new JwtFacade(clock: $this->clock);

            $parsed = $facade->parse(
                $token,
                new Constraint\SignedWith($this->signer, $this->key),
                new Constraint\StrictValidAt($this->clock),
            );

            /** @var array<non-empty-string, mixed> $claims */
            $claims = $parsed->claims()->all();

            $registeredKeys = RegisteredClaims::ALL;

            return array_diff_key($claims, array_flip($registeredKeys));
        } catch (\RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Invalid or expired token: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
