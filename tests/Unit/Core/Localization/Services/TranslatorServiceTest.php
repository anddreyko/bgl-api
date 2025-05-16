<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Localization\Services;

use App\Infrastructure\Localization\Translator;
use Codeception\Test\Unit;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * @covers \App\Infrastructure\Localization\Translator
 */
final class TranslatorServiceTest extends Unit
{
    private const DE_HELLO_WORLD_TRANS = ['Hello world!' => 'Hallo Welt!'];
    private const DE_HELLO_WORLD_DOMAIN = 'hello-world';

    private Translator $service;

    protected function setUp(): void
    {
        $translator = new SymfonyTranslator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', self::DE_HELLO_WORLD_TRANS, 'de', self::DE_HELLO_WORLD_DOMAIN);

        $this->service = new Translator($translator);

        parent::setUp();
    }

    public function testLocale(): void
    {
        $this->service->setLocale('fr');
        $this->assertEquals('fr', $this->service->getLocale());
    }

    public function testSuccess(): void
    {
        $this->service->setLocale('de');
        $this->assertEquals(
            'Hallo Welt!',
            $this->service->trans(id: 'Hello world!', domain: self::DE_HELLO_WORLD_DOMAIN)
        );
    }

    public function testNotExistTrans(): void
    {
        $this->service->setLocale('de');
        $this->assertEquals(
            'Good morning!',
            $this->service->trans(id: 'Good morning!', domain: self::DE_HELLO_WORLD_DOMAIN)
        );
    }

    public function testNotExistLang(): void
    {
        $this->service->setLocale('es');
        $this->assertEquals(
            'Hello world!',
            $this->service->trans(id: 'Hello world!', domain: self::DE_HELLO_WORLD_DOMAIN)
        );
    }
}
