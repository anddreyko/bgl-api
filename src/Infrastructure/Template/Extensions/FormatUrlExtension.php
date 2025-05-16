<?php

declare(strict_types=1);

namespace App\Infrastructure\Template\Extensions;

use App\Infrastructure\Http\ValueObjects\Url;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @see \Tests\Unit\Core\Templates\Extensions\FormatUrlExtensionTest
 */
final class FormatUrlExtension extends AbstractExtension
{
    public function __construct(private readonly Url $url)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'url']),
        ];
    }

    /**
     * @param string $path
     * @param array<array-key, mixed> $params
     *
     * @return string
     */
    public function url(string $path, array $params): string
    {
        return $this->url->convert(['path' => $path, 'query' => $params])->getUrl();
    }
}
