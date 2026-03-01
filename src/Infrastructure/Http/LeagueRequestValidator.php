<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\RequestValidator;
use Bgl\Core\Validation\ValidationErrors;
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
    public function validate(ServerRequestInterface $request): ValidationErrors
    {
        try {
            $this->validator->validate($request);

            return ValidationErrors::empty();
        } catch (NoOperation) {
            // Path not in spec -- skip validation (ApiAction handles 404 separately)
            return ValidationErrors::empty();
        } catch (ValidationFailed $e) {
            return $this->extractErrors($e);
        }
    }

    private function extractErrors(ValidationFailed $exception): ValidationErrors
    {
        $errors = [];
        $previous = $exception->getPrevious();

        if ($previous instanceof KeywordMismatch) {
            $this->collectKeywordErrors($previous, $errors);
        }

        if ($errors === []) {
            $errors['_general'][] = $exception->getMessage();
        }

        return ValidationErrors::fromArray($errors);
    }

    /**
     * @param array<string, list<string>> $errors
     */
    private function collectKeywordErrors(KeywordMismatch $exception, array &$errors): void
    {
        $breadCrumb = $exception->dataBreadCrumb();
        $chain = $breadCrumb !== null ? $breadCrumb->buildChain() : [];

        $field = $chain !== [] ? (string)end($chain) : $exception->keyword();

        $errors[$field][] = $exception->getMessage();

        $innerException = $exception->getPrevious();
        if ($innerException instanceof KeywordMismatch) {
            $this->collectKeywordErrors($innerException, $errors);
        }
    }
}
