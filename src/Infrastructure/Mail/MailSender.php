<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Infrastructure\Mail\Builders\MessageBuilder;
use App\Infrastructure\Template\Renders\BaseRender;
use App\Infrastructure\Template\TemplateRenderer;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @see \Tests\Unit\Core\Mail\Services\MailSenderServiceTest
 */
final readonly class MailSender
{
    public function __construct(private TemplateRenderer $renderer, private MailerInterface $mailer)
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
