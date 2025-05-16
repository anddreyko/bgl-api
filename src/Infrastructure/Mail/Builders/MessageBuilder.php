<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail\Builders;

use Symfony\Component\Mime\Email;

/**
 * @see \Tests\Unit\Core\Mail\Builders\MessageBuilderTest
 */
final readonly class MessageBuilder
{
    private Email $email;

    public function __construct()
    {
        $this->email = new Email();
    }

    public static function create(): self
    {
        return new self();
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function from(string $from): self
    {
        $this->email->from($from);

        return $this;
    }

    public function to(string $to): self
    {
        $this->email->to($to);

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->email->subject($subject);

        return $this;
    }

    public function text(string $text): self
    {
        $this->email->text($text);

        return $this;
    }

    public function html(string $html): self
    {
        $this->email->html($html);

        return $this;
    }
}
