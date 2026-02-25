<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\RequestValidator;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ServerRequestValidator as LeagueServerRequestValidator;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LeagueRequestValidator implements RequestValidator
{
    public function __construct(
        private LeagueServerRequestValidator $validator,
    ) {
    }

    #[\Override]
    public function validate(ServerRequestInterface $request, array $operation, array $pathParams = []): array
    {
        try {
            $this->validator->validate($request);

            return [];
        } catch (NoOperation) {
            // Path not in spec -- skip validation (ApiAction handles 404 separately)
            return [];
        } catch (ValidationFailed $e) {
            return $this->extractErrors($e);
        }
    }

    /**
     * @return array<string, string[]>
     */
    private function extractErrors(ValidationFailed $exception): array
    {
        $errors = [];
        $previous = $exception->getPrevious();

        if ($previous instanceof KeywordMismatch) {
            $this->collectKeywordErrors($previous, $errors);
        }

        if ($errors === []) {
            $errors['_general'][] = $exception->getMessage();
        }

        return $errors;
    }

    /**
     * @param array<string, string[]> $errors
     */
    private function collectKeywordErrors(KeywordMismatch $exception, array &$errors): void
    {
        $breadCrumb = $exception->dataBreadCrumb();
        $chain = $breadCrumb !== null ? $breadCrumb->buildChain() : [];

        $field = $chain !== [] ? (string) end($chain) : $exception->keyword();

        $errors[$field][] = $exception->getMessage();

        $innerException = $exception->getPrevious();
        if ($innerException instanceof KeywordMismatch) {
            $this->collectKeywordErrors($innerException, $errors);
        }
    }
}
