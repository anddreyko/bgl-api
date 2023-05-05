<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Services\Register;

use Codeception\Test\Unit;

/**
 * @covers \App\Auth\Services\Register\RegistrationByEmailService
 */
final class RegistrationByEmailServiceTest extends Unit
{
    public function testSuccess(): void
    {
        /*$email = new Email('test@test.test');

        $em = $this->makeEmpty(EntityManagerInterface::class);
        $users = $this->makeEmpty(
            UserRepository::class,
            ['hasByEmail' => Expected::once($email), 'add' => Expected::once()]
        );
        $hasher = $this->make(new PasswordHashHelper(16), ['hash' => Expected::once()]);

        $tokenizer = new TokenizerHelper(new \DateInterval('P1H'));
        $flusher = new FlushHelper($em);

        $render = $this->makeEmpty(RenderTemplateService::class);
        $mailer = $this->makeEmpty(MailerInterface::class);
        $sender = new MailSenderService($render, $mailer);
        $service = new RegistrationByEmailService($users, $hasher, $tokenizer, $flusher, $sender);

        $form = new RegistrationByEmailForm($email->getValue(), 'password');
        $service->handle($form);*/
    }
}
