<?php

declare(strict_types=1);

namespace App\Core\Template\Renders;

interface BaseRender
{
    public function pathToTemplate(): string;

    public function subject(): string;

    /**
     * @return mixed[]
     */
    public function params(): array;
}
