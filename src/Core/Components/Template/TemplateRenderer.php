<?php

declare(strict_types=1);

namespace App\Core\Components\Template;

use App\Core\Components\Template\Renders\BaseRender;
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
