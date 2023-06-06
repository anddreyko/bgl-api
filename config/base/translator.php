<?php

declare(strict_types=1);

use App\Core\Http\Services\AcceptLanguageService;
use App\Core\Localization\Services\TranslatorService;
use Kudashevs\AcceptLanguage\AcceptLanguage;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;

return [
    TranslatorService::class => static function (ContainerInterface $container) {
        /** @var array{lang: string, resources: string[][]} $config */
        $config = $container->get('translator');

        $translator = new Translator($config['lang']);
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addLoader('xlf', new XliffFileLoader());

        foreach ($config['resources'] as $resource) {
            $translator->addResource(...$resource);
        }

        return new TranslatorService($translator);
    },

    AcceptLanguageService::class => static function (ContainerInterface $container) {
        /** @var array{lang: string, accepted_lang: string[], two_letter_only: bool, exact_match_only: bool} $config */
        $config = $container->get('translator');

        return new AcceptLanguageService(
            new AcceptLanguage(
                [
                    'default_language' => $config['lang'],
                    'accepted_languages' => $config['accepted_lang'],
                    'two_letter_only' => $config['two_letter_only'],
                    'exact_match_only' => $config['exact_match_only'],
                ]
            )
        );
    },

    'translator' => [
        'lang' => 'en',
        'accepted_lang' => ['en', 'ru'],
        'two_letter_only' => true,
        'exact_match_only' => false,
        'resources' => [
            [
                'xlf',
                __DIR__ . '/../../vendor/symfony/validator/Resources/translations/validators.ru.xlf',
                'ru',
                'validators',
            ],
            [
                'php',
                __DIR__ . '/../../translations/exceptions.ru.php',
                'ru',
                'exceptions',
            ],
            [
                'php',
                __DIR__ . '/../../translations/hello-world.ru.php',
                'ru',
                'hello-world',
            ],
        ],
    ],
];
