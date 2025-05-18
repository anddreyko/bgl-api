<?php

declare(strict_types=1);

use App\Infrastructure\Localization\Translator;
use DI\Container;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

return [
    ValidatorInterface::class => static function (Container $container) {
        /** @var Translator $translator */
        $translator = $container->get(Translator::class);

        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();
    },
];
