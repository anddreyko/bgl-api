<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Kudashevs\AcceptLanguage\AcceptLanguage;

/**
 * @see \Tests\Unit\Core\Http\Services\AcceptLanguageServiceTest
 */
final readonly class LanguageAcceptor
{
    public function __construct(private AcceptLanguage $language)
    {
    }

    public function handle(): string
    {
        $this->language->process();

        return $this->language->getLanguage();
    }
}
