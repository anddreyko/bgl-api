<?php

declare(strict_types=1);

use App\Core\Mail\Services\MailSenderService;
use App\Core\Template\Services\RenderTemplateService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

return [
    MailSenderService::class => static function (ContainerInterface $container) {
        /** @var array{ sftp: string } $config */
        $config = $container->get('mailer');
        /** @var RenderTemplateService $renderer */
        $renderer = $container->get(RenderTemplateService::class);

        return new MailSenderService($renderer, new Mailer(Transport::fromDsn($config['sftp'])));
    },

    'mailer' => [
        'sftp' => env('MAILER_DSN'),
    ],
];
