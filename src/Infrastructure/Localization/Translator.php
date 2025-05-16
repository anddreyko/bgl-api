<?php

declare(strict_types=1);

namespace App\Infrastructure\Localization;

use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see \Tests\Unit\Core\Localization\Services\TranslatorServiceTest
 */
class Translator implements TranslatorInterface
{
    public function __construct(private readonly SymfonyTranslator $translator)
    {
    }

    /**
     * @param string $id
     * @param array<array-key, mixed> $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans(id: $id, parameters: $parameters, domain: $domain, locale: $locale);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}
