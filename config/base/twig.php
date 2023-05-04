<?php

declare(strict_types=1);

use App\Core\Template\Extensions\FormatUrlExtension;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

return [
    Environment::class => static function (ContainerInterface $container) {
        /**
         * @var array{
         *     templates_path:string[],
         *     cache_path:string,
         *     debug: bool,
         *     extensions: array<class-string<Twig\Extension\ExtensionInterface>>
         * } $config
         */
        $config = $container->get('twig');
        $loader = new FilesystemLoader($config['templates_path']);

        $environment = new Environment(
            $loader,
            [
                'cache' => $config['debug'] ? false : $config['cache_path'],
                'debug' => $config['debug'],
                'strict_variables' => $config['debug'],
                'auto_reload' => $config['debug'],
            ]
        );

        if ($config['debug']) {
            $environment->addExtension(new DebugExtension());
        }

        foreach ($config['extensions'] as $class) {
            /** @var \Twig\Extension\ExtensionInterface $extension */
            $extension = $container->get($class);
            $environment->addExtension($extension);
        }

        return $environment;
    },

    'twig' => [
        'debug' => (bool)getenv('APP_DEBUG'),
        'templates_path' => [__DIR__ . '/../../templates'],
        'cache_path' => __DIR__ . '/../../var/cache/twig',
        'extensions' => [
            FormatUrlExtension::class,
        ],
    ],
];
