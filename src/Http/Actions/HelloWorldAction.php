<?php

declare(strict_types=1);

namespace App\Http\Actions;

class HelloWorldAction extends BaseAction
{
    public function content(): \stdClass
    {
        $content = new \stdClass();
        $content->content = 'Hello world!';

        return $content;
    }
}
