<?php

declare(strict_types=1);

use App\Infrastructure\Mail\MailSender;
use App\Infrastructure\Template\TemplateRenderer;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

return [
    MailSender::class => static function (ContainerInterface $container) {
        /** @var array{ sftp: string } $config */
        $config = $container->get('mailer');
        /** @var TemplateRenderer $renderer */
        $renderer = $container->get(TemplateRenderer::class);

        return new MailSender($renderer, new Mailer(Transport::fromDsn($config['sftp'])));
    },

    'mailer' => [
        'sftp' => env('MAILER_DSN'),
    ],
];
