<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Mail\Services;

use App\Core\Mail\Builders\MessageBuilder;
use App\Core\Mail\Services\MailSenderService;
use App\Core\Template\Renders\BaseRender;
use App\Core\Template\Services\RenderTemplateService;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

/**
 * @covers \App\Core\Mail\Services\MailSenderService
 */
final class MailSenderServiceTest extends Unit
{
    public function testSend(): void
    {
        $template = $this->createStub(Environment::class);
        $templateService = new RenderTemplateService($template);
        $mailer = $this->makeEmpty(MailerInterface::class, ['send' => Expected::once()]);
        $service = new MailSenderService($templateService, $mailer);

        $message = MessageBuilder::create();
        $render = $this->createStub(BaseRender::class);

        $service->send($message, $render);
    }
}
