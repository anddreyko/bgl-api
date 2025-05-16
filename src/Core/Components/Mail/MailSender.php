<?php

declare(strict_types=1);

namespace App\Core\Components\Mail;

use App\Core\Components\Mail\Builders\MessageBuilder;
use App\Core\Components\Template\Renders\BaseRender;
use App\Core\Components\Template\TemplateRenderer;
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
