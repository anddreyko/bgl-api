<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Templates\Extensions;

use App\Infrastructure\Http\ValueObjects\Url;
use App\Infrastructure\Template\Extensions\FormatUrlExtension;
use Codeception\Test\Unit;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @covers \App\Infrastructure\Template\Extensions\FormatUrlExtension
 */
final class FormatUrlExtensionTest extends Unit
{
    public function testSuccess(): void
    {
        $url = $this->createMock(Url::class);

        $willParams = ['path' => 'path', 'query' => ['param_1' => 123, 'param_2' => '456']];
        $url->expects($this->once())
            ->method('convert')
            ->with($this->equalTo($willParams))
            ->willReturn(Url::convertFromArray($willParams));

        $template = new Environment(
            new ArrayLoader(['function-url.twig' => "<p>{{ url('path', { param_1: 123, param_2: '456' }) }}</p>"])
        );
        $template->addExtension(new FormatUrlExtension($url));

        $this->assertEquals('<p>/path?param_1=123&amp;param_2=456</p>', $template->render('function-url.twig'));
    }
}
