<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\PasskeySignInVerify;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\PasskeyVerifier;
use Bgl\Core\Auth\TokenIssuer;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Messages\MessageHandler;
use Bgl\Domain\Profile\Passkey\PasskeyChallenges;
use Bgl\Domain\Profile\Passkey\Passkeys;

/**
 * @implements MessageHandler<Result, Command>
 */
final readonly class Handler implements MessageHandler
{
    public function __construct(
        private Passkeys $passkeys,
        private PasskeyChallenges $challenges,
        private PasskeyVerifier $passkeyVerifier,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    #[\Override]
    public function __invoke(Envelope $envelope): Result
    {
        /** @var Command $command */
        $command = $envelope->message;

        $decoded = $this->decodeResponse($command->response);

        $passkey = $this->passkeys->findByCredentialId($decoded['id'])
            ?? throw new AuthenticationException('Passkey not found');

        $challenge = $this->findValidChallenge($decoded);

        $newCounter = $this->passkeyVerifier->login(
            $command->response,
            $challenge->getChallenge(),
            $passkey->getCredentialData(),
        );

        $passkey->updateCounter($newCounter);
        $this->challenges->remove($challenge);

        $userId = $passkey->getUserId()->getValue()
            ?? throw new AuthenticationException('Unauthorized');

        $tokenPair = $this->tokenIssuer->issue($userId);

        return new Result(
            accessToken: $tokenPair->accessToken,
            refreshToken: $tokenPair->refreshToken,
        );
    }

    /**
     * @return array{id: string}&array<array-key, mixed>
     */
    private function decodeResponse(string $response): array
    {
        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !isset($decoded['id']) || !is_string($decoded['id'])) {
            throw new AuthenticationException('Invalid response format');
        }

        return $decoded;
    }

    /**
     * @param array<array-key, mixed> $decoded
     */
    private function findValidChallenge(array $decoded): \Bgl\Domain\Profile\Passkey\PasskeyChallenge
    {
        // The clientDataJSON contains the challenge -- extract it
        if (isset($decoded['response']['clientDataJSON']) && is_string($decoded['response']['clientDataJSON'])) {
            /** @var mixed $clientData */
            $clientData = json_decode(
                base64_decode($decoded['response']['clientDataJSON']),
                true,
            );

            if (is_array($clientData) && isset($clientData['challenge']) && is_string($clientData['challenge'])) {
                $challenge = $this->challenges->findByChallenge($clientData['challenge']);
                if ($challenge !== null) {
                    return $challenge;
                }
            }
        }

        throw new AuthenticationException('Challenge not found or expired');
    }
}
