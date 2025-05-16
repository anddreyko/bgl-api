<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Mail\Builders;

use App\Infrastructure\Mail\Builders\MessageBuilder;
use Codeception\Test\Unit;

/**
 * @covers \App\Infrastructure\Mail\Builders\MessageBuilder
 */
final class MessageBuilderTest extends Unit
{
    private MessageBuilder $builder;

    public function _before(): void
    {
        $this->builder = MessageBuilder::create();
        parent::_before();
    }

    public function testFrom(): void
    {
        $this->builder->from('no-reply@test.com');

        $this->assertEquals('no-reply@test.com', current($this->builder->getEmail()->getFrom())->toString());
    }

    public function testTo(): void
    {
        $this->builder->to('some-user@test.com');

        $this->assertEquals('some-user@test.com', current($this->builder->getEmail()->getTo())->toString());
    }

    public function testSubject(): void
    {
        $this->builder->subject('some subject');

        $this->assertEquals('some subject', $this->builder->getEmail()->getSubject());
    }

    public function testText(): void
    {
        $this->builder->text('some text');

        $this->assertEquals('some text', $this->builder->getEmail()->getTextBody());
    }

    public function testHtml(): void
    {
        $this->builder->html('<div>some text</div>');

        $this->assertEquals('<div>some text</div>', $this->builder->getEmail()->getHtmlBody());
    }
}
