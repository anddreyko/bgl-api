<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Mail\Services;

use App\Infrastructure\Mail\Builders\MessageBuilder;
use App\Infrastructure\Mail\MailSender;
use App\Infrastructure\Template\Renders\BaseRender;
use App\Infrastructure\Template\TemplateRenderer;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

/**
 * @covers \App\Infrastructure\Mail\MailSender
 */
final class MailSenderServiceTest extends Unit
{
    public function testSend(): void
    {
        $template = $this->createStub(Environment::class);
        $templateService = new TemplateRenderer($template);
        $mailer = $this->makeEmpty(MailerInterface::class, ['send' => Expected::once()]);
        $service = new MailSender($templateService, $mailer);

        $message = MessageBuilder::create();
        $render = $this->createStub(BaseRender::class);

        $service->send($message, $render);
    }
}
