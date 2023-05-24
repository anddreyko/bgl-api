<?php

declare(strict_types=1);

namespace Actions\V1\Auth;

use App\Auth\Forms\RegistrationByEmailForm;
use App\Auth\Services\Register\RegistrationByEmailService;
use App\Core\Http\Actions\BaseAction;
use App\Core\Http\Entities\Response;
use App\Core\Http\Enums\HttpCodesEnum;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;

/**
 * @see \Tests\Api\V1\Auth\SignUpCest
 */
final class SignUpAction extends BaseAction
{
    public function __construct(
        private readonly ResponseFactoryInterface $factory,
        private readonly RegistrationByEmailService $service
    ) {
        parent::__construct($this->factory);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->service->handle(
                new RegistrationByEmailForm(
                    (string)($request->getQueryParams()['email'] ?? ''),
                    (string)($request->getQueryParams()['password'] ?? ''),
                )
            );
        } catch (\Exception $exception) {
            $exception = new HttpException(
                $request,
                $exception->getMessage(),
                (int)($exception->getCode() ?: HttpCodesEnum::InternalServerError->value),
                $exception
            );
            $exception->setTitle($exception->getMessage() ?: 'Unexpected error');

            throw $exception;
        }

        return parent::handle($request);
    }

    /**
     * @OpenApi\Annotations\Get(
     *     path="/v1/auth/register-by-email",
     *     @OA\Response(
     *         response="200",
     *         description="Register by email"
     *     )
     * )
     */
    public function content(): Response
    {
        return new Response(data: 'Confirm the specified email', result: true);
    }
}
