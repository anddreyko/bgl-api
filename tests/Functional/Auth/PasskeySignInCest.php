<?php

declare(strict_types=1);

namespace Bgl\Tests\Functional\Auth;

use Bgl\Application\Handlers\Auth\PasskeySignInOptions\Command as SignInOptionsCommand;
use Bgl\Application\Handlers\Auth\PasskeySignInOptions\Handler as SignInOptionsHandler;
use Bgl\Application\Handlers\Auth\PasskeySignInOptions\Result as SignInOptionsResult;
use Bgl\Application\Handlers\Auth\PasskeySignInVerify\Command as SignInVerifyCommand;
use Bgl\Application\Handlers\Auth\PasskeySignInVerify\Handler as SignInVerifyHandler;
use Bgl\Application\Handlers\Auth\PasskeySignInVerify\Result as SignInVerifyResult;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\Passkey;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\Passkeys;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\FunctionalTester;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\PasskeySignInOptions\Handler
 * @covers \Bgl\Application\Handlers\Auth\PasskeySignInVerify\Handler
 */
#[Group('application', 'handler', 'passkey')]
final class PasskeySignInCest
{
    private EntityManagerInterface $em;
    private SignInOptionsHandler $optionsHandler;
    private SignInVerifyHandler $verifyHandler;
    private Users $users;
    private Passkeys $passkeys;
    private PasskeyChallenges $challenges;
    private UuidGenerator $uuidGenerator;

    public function _before(): void
    {
        $container = DiHelper::container();

        /** @var EntityManagerInterface $em */
        $this->em = $container->get(EntityManagerInterface::class);

        /** @var SignInOptionsHandler $optionsHandler */
        $this->optionsHandler = $container->get(SignInOptionsHandler::class);

        /** @var SignInVerifyHandler $verifyHandler */
        $this->verifyHandler = $container->get(SignInVerifyHandler::class);

        /** @var Users $users */
        $this->users = $container->get(Users::class);

        /** @var Passkeys $passkeys */
        $this->passkeys = $container->get(Passkeys::class);

        /** @var PasskeyChallenges $challenges */
        $this->challenges = $container->get(PasskeyChallenges::class);

        /** @var UuidGenerator $uuidGenerator */
        $this->uuidGenerator = $container->get(UuidGenerator::class);
    }

    public function testSignInOptionsReturnsJson(FunctionalTester $i): void
    {
        /** @var SignInOptionsResult $result */
        $result = ($this->optionsHandler)(new Envelope(
            message: new SignInOptionsCommand(),
            messageId: 'test-msg-id',
        ));

        $this->em->flush();

        $i->assertNotEmpty($result->options);
        $i->assertStringContainsString('challenge', $result->options);
    }

    public function testSignInOptionsSavesChallenge(FunctionalTester $i): void
    {
        ($this->optionsHandler)(new Envelope(
            message: new SignInOptionsCommand(),
            messageId: 'test-msg-id',
        ));

        $this->em->flush();

        $results = iterator_to_array($this->challenges->search(All::Filter));
        $i->assertNotEmpty($results);
    }

    public function testSignInVerifyPasskeyNotFoundThrows(FunctionalTester $i): void
    {
        $response = $this->makeWebAuthnResponse('unknown-cred', 'fake-challenge');

        $i->expectThrowable(
            AuthenticationException::class,
            fn () => ($this->verifyHandler)(new Envelope(
                message: new SignInVerifyCommand(response: $response),
                messageId: 'test-msg-id',
            )),
        );
    }

    private function makeWebAuthnResponse(string $credentialId, string $challenge): string
    {
        $clientDataJSON = base64_encode(json_encode([
            'type' => 'webauthn.get',
            'challenge' => $challenge,
            'origin' => 'https://example.com',
        ], JSON_THROW_ON_ERROR));

        return json_encode([
            'id' => $credentialId,
            'response' => [
                'clientDataJSON' => $clientDataJSON,
                'authenticatorData' => base64_encode('fake-auth-data'),
                'signature' => base64_encode('fake-signature'),
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
