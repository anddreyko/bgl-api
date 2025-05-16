<?php

declare(strict_types=1);

namespace App\Domain\Auth\Renders;

use App\Core\ValueObjects\Token;
use App\Infrastructure\Template\Renders\BaseRender;

final readonly class ConfirmEmailRender implements BaseRender
{
    public function __construct(private Token $token)
    {
    }

    public function pathToTemplate(): string
    {
        return '/auth/confirm-email.twig';
    }

    /**
     * @return array{ confirm_link: string, title: string }
     */
    public function params(): array
    {
        return [
            'confirm_link' => (string)env('FRONTEND_URL') . '/v1/auth/confirm-by-email/' . $this->token->getValue(),
            'title' => $this->subject(),
        ];
    }

    public function subject(): string
    {
        return 'Confirm your email';
    }
}
