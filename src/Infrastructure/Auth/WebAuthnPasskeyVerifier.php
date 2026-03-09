<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\CredentialResult;
use Bgl\Core\Auth\PasskeyOptions;
use Bgl\Core\Auth\PasskeyVerifier;
use lbuchs\WebAuthn\WebAuthn;

final readonly class WebAuthnPasskeyVerifier implements PasskeyVerifier
{
    public function __construct(
        private string $rpId,
        private string $rpName,
    ) {
    }

    #[\Override]
    public function registerOptions(string $userId, string $userName): PasskeyOptions
    {
        $webAuthn = $this->createWebAuthn();

        /** @var \stdClass $args */
        $args = $webAuthn->getCreateArgs(
            $userId,
            $userName,
            $userName,
            timeout: 60,
            requireResidentKey: true,
        );

        $challenge = $webAuthn->getChallenge()->getBinaryString();

        return new PasskeyOptions(
            optionsJson: json_encode($args, JSON_THROW_ON_ERROR),
            challenge: base64_encode($challenge),
        );
    }

    #[\Override]
    public function register(string $response, string $challenge): CredentialResult
    {
        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new AuthenticationException('Invalid response format');
            }

            /** @var string $clientDataJSON */
            $clientDataJSON = $decoded['response']['clientDataJSON'] ?? '';
            /** @var string $attestationObject */
            $attestationObject = $decoded['response']['attestationObject'] ?? '';

            $webAuthn = $this->createWebAuthn();

            /** @var \stdClass $result */
            $result = $webAuthn->processCreate(
                self::base64urlDecode($clientDataJSON),
                self::base64urlDecode($attestationObject),
                base64_decode($challenge),
                requireUserVerification: false,
                failIfRootMismatch: false,
            );

            return new CredentialResult(
                credentialId: base64_encode((string)$result->credentialId),
                credentialData: base64_encode((string)$result->credentialPublicKey),
            );
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AuthenticationException('Registration failed: ' . $e->getMessage(), 0, $e);
        }
    }

    #[\Override]
    public function loginOptions(): PasskeyOptions
    {
        $webAuthn = $this->createWebAuthn();

        /** @var \stdClass $args */
        $args = $webAuthn->getGetArgs(
            credentialIds: [],
            timeout: 60,
            requireUserVerification: false,
        );

        $challenge = $webAuthn->getChallenge()->getBinaryString();

        return new PasskeyOptions(
            optionsJson: json_encode($args, JSON_THROW_ON_ERROR),
            challenge: base64_encode($challenge),
        );
    }

    #[\Override]
    public function login(string $response, string $challenge, string $credentialData): int
    {
        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new AuthenticationException('Invalid response format');
            }

            /** @var string $clientDataJSON */
            $clientDataJSON = $decoded['response']['clientDataJSON'] ?? '';
            /** @var string $authenticatorData */
            $authenticatorData = $decoded['response']['authenticatorData'] ?? '';
            /** @var string $signature */
            $signature = $decoded['response']['signature'] ?? '';

            $webAuthn = $this->createWebAuthn();

            $webAuthn->processGet(
                self::base64urlDecode($clientDataJSON),
                self::base64urlDecode($authenticatorData),
                self::base64urlDecode($signature),
                base64_decode($credentialData),
                base64_decode($challenge),
                requireUserVerification: false,
            );

            return $webAuthn->getSignatureCounter() ?? 0;
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AuthenticationException('Login failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    private function createWebAuthn(): WebAuthn
    {
        return new WebAuthn($this->rpName, $this->rpId, useBase64UrlEncoding: true);
    }
}
