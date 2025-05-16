<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Templates\Services;

use App\Infrastructure\Template\Renders\BaseRender;
use App\Infrastructure\Template\TemplateRenderer;
use Codeception\Test\Unit;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class RenderTemplateServiceTest extends Unit
{
    public function testRender(): void
    {
        $template = new Environment(new ArrayLoader(['test.twig' => "<p>{{ param }}</p>"]));
        $service = new TemplateRenderer($template);

        $render = $this->createStub(BaseRender::class);
        $render->method('pathToTemplate')->willReturn('test.twig');
        $render->method('params')->willReturn(['param' => 'test']);

        $this->assertEquals('<p>test</p>', $service->render($render));
    }
}
