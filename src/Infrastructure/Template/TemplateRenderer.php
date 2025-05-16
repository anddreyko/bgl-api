<?php

declare(strict_types=1);

namespace App\Infrastructure\Template;

use App\Infrastructure\Template\Renders\BaseRender;
use Twig\Environment;

final readonly class TemplateRenderer
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(BaseRender $render): string
    {
        return $this->twig->render($render->pathToTemplate(), $render->params());
    }
}
