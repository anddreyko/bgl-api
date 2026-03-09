<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\CredentialResult;
use Bgl\Core\Auth\PasskeyOptions;
use Bgl\Core\Auth\PasskeyVerifier;
use lbuchs\WebAuthn\Binary\ByteBuffer;
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
                base64_decode($clientDataJSON),
                base64_decode($attestationObject),
                base64_decode($challenge),
                requireUserVerification: false,
                failIfRootMismatch: false,
            );

            /** @var \lbuchs\WebAuthn\Binary\ByteBuffer $credentialId */
            $credentialId = $result->credentialId;

            return new CredentialResult(
                credentialId: base64_encode($credentialId->getBinaryString()),
                credentialData: (string)$result->credentialPublicKey,
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
                base64_decode($clientDataJSON),
                base64_decode($authenticatorData),
                base64_decode($signature),
                $credentialData,
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

    private function createWebAuthn(): WebAuthn
    {
        ByteBuffer::$useBase64UrlEncoding = true;

        return new WebAuthn($this->rpName, $this->rpId);
    }
}
