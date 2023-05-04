<?php

declare(strict_types=1);

namespace App\Core\Mail\Services;

use App\Core\Mail\Builders\MessageBuilder;
use App\Core\Template\Renders\BaseRender;
use App\Core\Template\Services\RenderTemplateService;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @see \Tests\Unit\Core\Mail\Services\MailSenderServiceTest
 */
final readonly class MailSenderService
{
    public function __construct(private RenderTemplateService $renderer, private MailerInterface $mailer)
    {
    }

    public function send(MessageBuilder $message, ?BaseRender $render = null): void
    {
        if ($render) {
            $message->subject($render->subject());
            $message->html($this->renderer->render($render));
        }

        $this->mailer->send($message->getEmail());
    }
}
