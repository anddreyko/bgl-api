<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api;

use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Exceptions\NotFoundException;
use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Http\SchemaMapper;
use Bgl\Core\Messages\Dispatcher;
use Bgl\Core\Serialization\Serializer;
use Bgl\Presentation\Api\V1\Responses\ErrorResponse;
use Bgl\Presentation\Api\V1\Responses\SuccessResponse;
use EventSauce\ObjectHydrator\ObjectMapper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ApiAction
{
    public function __construct(
        private CompiledRouteMap $routeMap,
        private InterceptorPipeline $interceptorPipeline,
        private RequestValidator $requestValidator,
        private SchemaMapper $schemaMapper,
        private ObjectMapper $hydrator,
        private Dispatcher $dispatcher,
        private Serializer $serializer,
        private ResponseFactoryInterface $responseFactory,
        private bool $debugMode,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->doHandle($request);
        } catch (AuthenticationException $e) {
            return $this->jsonResponse(
                new ErrorResponse(message: $e->getMessage(), httpStatus: 401),
            );
        } catch (NotFoundException $e) {
            return $this->jsonResponse(
                new ErrorResponse(message: $e->getMessage(), httpStatus: 404),
            );
        } catch (\DomainException $e) {
            return $this->jsonResponse(
                new ErrorResponse(message: $e->getMessage(), httpStatus: 400),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse(
                ErrorResponse::validation(message: $e->getMessage(), errors: []),
            );
        } catch (\Throwable $e) {
            $error = $this->debugMode
                ? ErrorResponse::serverError(message: $e->getMessage(), exception: $e)
                : new ErrorResponse(message: 'Internal Server Error', httpStatus: 500);

            return $this->jsonResponse($error);
        }
    }

    private function doHandle(ServerRequestInterface $request): ResponseInterface
    {
        $matchResult = $this->routeMap->match($request->getMethod(), $request->getUri()->getPath());
        if ($matchResult === null) {
            return $this->jsonResponse(new ErrorResponse(message: 'Not Found', httpStatus: 404));
        }

        $operation = $matchResult->operation;
        $request = $this->interceptorPipeline->process($request, $operation->interceptors);

        $validationErrors = $this->requestValidator->validate($request);
        if ($validationErrors !== []) {
            return $this->jsonResponse(
                ErrorResponse::validation(message: 'Validation failed', errors: $validationErrors)
            );
        }

        $data = $this->schemaMapper->map(
            $request,
            $matchResult->pathParams,
            $operation->authParams,
            $operation->paramMap
        );

        return $this->dispatchAndRespond($operation->messageClass, $data);
    }

    /**
     * @param class-string<\Bgl\Core\Messages\Message> $messageClass
     * @param array<string, mixed> $data
     */
    private function dispatchAndRespond(string $messageClass, array $data): ResponseInterface
    {
        /** @var \Bgl\Core\Messages\Message $message */
        $message = $this->hydrator->hydrateObject($messageClass, $data);
        /** @var mixed $result */
        $result = $this->dispatcher->dispatch($message);

        /** @var mixed $responseData */
        $responseData = is_object($result) ? $this->serializer->serialize($result) : $result;

        return $this->jsonResponse(new SuccessResponse(data: $responseData));
    }

    private function jsonResponse(SuccessResponse|ErrorResponse $responseData): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($responseData->httpStatus);
        $response = $response->withHeader('Content-Type', 'application/json');

        $payload = self::buildPayload($responseData);

        $response->getBody()->write(json_encode($payload, JSON_THROW_ON_ERROR));

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildPayload(SuccessResponse|ErrorResponse $responseData): array
    {
        if ($responseData instanceof ErrorResponse) {
            $payload = [
                'code' => $responseData->code,
                'message' => $responseData->message,
            ];

            if ($responseData->errors !== []) {
                $payload['errors'] = $responseData->errors;
            }

            if ($responseData->exception !== null) {
                $payload['exception'] = [
                    'class' => $responseData->exception::class,
                    'message' => $responseData->exception->getMessage(),
                    'trace' => $responseData->exception->getTraceAsString(),
                ];
            }

            return $payload;
        }

        return [
            'code' => $responseData->code,
            'data' => $responseData->data,
        ];
    }
}
