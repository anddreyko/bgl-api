<?php

declare(strict_types=1);

namespace App\Core\Template\Services;

use App\Core\Template\Renders\BaseRender;
use Twig\Environment;

final readonly class RenderTemplateService
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(BaseRender $render): string
    {
        return $this->twig->render($render->pathToTemplate(), $render->params());
    }
}
