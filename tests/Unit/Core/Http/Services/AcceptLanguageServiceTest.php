<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Services;

use App\Infrastructure\Http\LanguageAcceptor;
use Codeception\Test\Unit;
use Kudashevs\AcceptLanguage\AcceptLanguage;

/**
 * @covers \App\Infrastructure\Http\LanguageAcceptor
 */
final class AcceptLanguageServiceTest extends Unit
{
    private LanguageAcceptor $service;

    protected function setUp(): void
    {
        $this->service = new LanguageAcceptor(
            new AcceptLanguage([
                'default_language' => 'en',
                'accepted_languages' => ['en', 'de'],
                'two_letter_only' => true,
            ])
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;
    }

    public function testSuccess(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';

        $this->assertEquals('en', $this->service->handle());
    }

    public function testLocationUnderStroke(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de_DE';

        $this->assertEquals('de', $this->service->handle());
    }

    public function testLocationDish(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE';

        $this->assertEquals('de', $this->service->handle());
    }

    public function testPriorityOne(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de, en';

        $this->assertEquals('de', $this->service->handle());
    }

    public function testPriorityTwo(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es, de, en';

        $this->assertEquals('de', $this->service->handle());
    }

    public function testNotSupported(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es';

        $this->assertEquals('en', $this->service->handle());
    }

    public function testOther(): void
    {
        @$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';

        $this->assertEquals('de', $this->service->handle());
    }
}
