<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Auth;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\PasskeyOptions;
use Bgl\Infrastructure\Auth\WebAuthnPasskeyVerifier;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Auth\WebAuthnPasskeyVerifier
 */
#[Group('unit', 'passkey', 'webauthn')]
final class WebAuthnPasskeyVerifierCest
{
    private WebAuthnPasskeyVerifier $verifier;

    public function _before(): void
    {
        $this->verifier = new WebAuthnPasskeyVerifier(
            rpId: 'localhost',
            rpName: 'TestApp',
        );
    }

    public function testRegisterOptionsReturnsBase64UrlChallenge(UnitTester $i): void
    {
        $options = $this->verifier->registerOptions('user-id-123', 'testuser');

        $i->assertInstanceOf(PasskeyOptions::class, $options);
        $i->assertNotEmpty($options->challenge);
        $i->assertNotEmpty($options->optionsJson);

        // Challenge must be valid base64
        $binary = base64_decode($options->challenge, true);
        $i->assertNotFalse($binary);
        $i->assertSame(32, strlen($binary));

        // Options JSON must contain base64url challenge (no MIME =?BINARY?B? wrapper)
        $i->assertStringNotContainsString('=?BINARY?B?', $options->optionsJson);

        $json = json_decode($options->optionsJson, true);
        $i->assertIsArray($json);
        $i->assertArrayHasKey('publicKey', $json);
        $i->assertArrayHasKey('challenge', $json['publicKey']);

        // Challenge in JSON must be base64url (only [A-Za-z0-9_-] chars)
        $jsonChallenge = $json['publicKey']['challenge'];
        $i->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $jsonChallenge);
    }

    public function testLoginOptionsReturnsBase64UrlChallenge(UnitTester $i): void
    {
        $options = $this->verifier->loginOptions();

        $i->assertInstanceOf(PasskeyOptions::class, $options);
        $i->assertStringNotContainsString('=?BINARY?B?', $options->optionsJson);

        $json = json_decode($options->optionsJson, true);
        $i->assertIsArray($json);
        $i->assertArrayHasKey('publicKey', $json);
        $i->assertArrayHasKey('challenge', $json['publicKey']);
        $i->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $json['publicKey']['challenge']);
    }

    public function testRegisterWithInvalidResponseThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            AuthenticationException::class,
            fn () => $this->verifier->register('not-json', base64_encode(random_bytes(32))),
        );
    }

    public function testRegisterWithEmptyResponseFieldsThrows(UnitTester $i): void
    {
        $response = json_encode([
            'response' => [
                'clientDataJSON' => '',
                'attestationObject' => '',
            ],
        ], JSON_THROW_ON_ERROR);

        $i->expectThrowable(
            AuthenticationException::class,
            fn () => $this->verifier->register($response, base64_encode(random_bytes(32))),
        );
    }

    public function testLoginWithInvalidResponseThrows(UnitTester $i): void
    {
        $i->expectThrowable(
            AuthenticationException::class,
            fn () => $this->verifier->login(
                'not-json',
                base64_encode(random_bytes(32)),
                base64_encode('fake-key'),
            ),
        );
    }

    public function testRegisterOptionsChallengeBinaryMatchesJsonChallenge(UnitTester $i): void
    {
        $options = $this->verifier->registerOptions('uid', 'uname');

        $json = json_decode($options->optionsJson, true);
        $jsonChallenge = $json['publicKey']['challenge'];

        // Decode base64url from JSON
        $jsonBinary = base64_decode(strtr($jsonChallenge, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($jsonChallenge)) % 4));
        // Decode standard base64 from stored challenge
        $storedBinary = base64_decode($options->challenge);

        $i->assertSame(bin2hex($storedBinary), bin2hex($jsonBinary));
    }

    public function testOptionsJsonHasNoMimeEncoding(UnitTester $i): void
    {
        $options = $this->verifier->registerOptions('user-id', 'user-name');

        // No MIME =?BINARY?B?...?= anywhere in the JSON
        $i->assertStringNotContainsString('=?BINARY?B?', $options->optionsJson);

        // Verify user.id in options is also base64url (not MIME)
        $json = json_decode($options->optionsJson, true);
        $i->assertIsArray($json);
        $userId = $json['publicKey']['user']['id'] ?? '';
        $i->assertStringNotContainsString('=?BINARY?B?', $userId);
    }
}
