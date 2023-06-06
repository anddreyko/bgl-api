<?php

declare(strict_types=1);

use App\Core\Localization\Services\TranslatorService;
use DI\Container;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

return [
    ValidatorInterface::class => static function (Container $container) {
        /** @var TranslatorService $translator */
        $translator = $container->get(TranslatorService::class);

        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();
    },
];
