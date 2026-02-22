<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Http;

use Bgl\Core\Http\RequestValidator;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OpenApiRequestValidator implements RequestValidator
{
    #[\Override]
    public function validate(ServerRequestInterface $request, array $operation, array $pathParams = []): array
    {
        $errors = [];

        $errors = $this->validateRequestBody($request, $operation, $errors);
        $errors = $this->validateParameters($request, $operation, $pathParams, $errors);

        return $errors;
    }

    /**
     * @param array<string, mixed> $operation
     * @param array<string, string[]> $errors
     *
     * @return array<string, string[]> Updated errors
     */
    private function validateRequestBody(
        ServerRequestInterface $request,
        array $operation,
        array $errors,
    ): array {
        $schema = $this->extractBodySchema($operation);

        if ($schema === null) {
            return $errors;
        }

        /** @var array<string, mixed> $body */
        $body = (array) ($request->getParsedBody() ?? []);

        /** @var list<string> $required */
        $required = isset($schema['required']) && is_array($schema['required'])
            ? $schema['required']
            : [];

        foreach ($required as $field) {
            if (!array_key_exists($field, $body) || $body[$field] === '' || $body[$field] === null) {
                $errors[$field][] = sprintf('The %s field is required', $field);
            }
        }

        /** @var array<string, array<string, mixed>> $properties */
        $properties = isset($schema['properties']) && is_array($schema['properties'])
            ? $schema['properties']
            : [];

        foreach ($properties as $field => $fieldSchema) {
            if (!array_key_exists($field, $body) || $body[$field] === null) {
                continue;
            }

            $fieldErrors = $this->validateFieldType($field, $body[$field], $fieldSchema);
            foreach ($fieldErrors as $fieldError) {
                $errors[$field][] = $fieldError;
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $operation
     * @param array<string, string> $pathParams
     * @param array<string, string[]> $errors
     *
     * @return array<string, string[]>
     */
    private function validateParameters(
        ServerRequestInterface $request,
        array $operation,
        array $pathParams,
        array $errors,
    ): array {
        /** @var mixed $parameters */
        $parameters = $operation['parameters'] ?? null;

        if (!is_array($parameters)) {
            return $errors;
        }

        /** @var array<string, string> $queryParams */
        $queryParams = $request->getQueryParams();

        foreach ($parameters as $param) {
            if (!is_array($param)) {
                continue;
            }

            /** @var mixed $name */
            $name = $param['name'] ?? null;
            /** @var mixed $in */
            $in = $param['in'] ?? null;
            $required = isset($param['required']) && $param['required'] === true;

            if (!is_string($name) || !is_string($in)) {
                continue;
            }

            $value = match ($in) {
                'query' => $queryParams[$name] ?? null,
                'path' => $pathParams[$name] ?? null,
                default => null,
            };

            if ($required && ($value === null || $value === '')) {
                $errors[$name][] = sprintf('The %s parameter is required', $name);
                continue;
            }

            if ($value === null) {
                continue;
            }

            /** @var array<string, mixed> $paramSchema */
            $paramSchema = isset($param['schema']) && is_array($param['schema'])
                ? $param['schema']
                : [];

            if ($paramSchema !== []) {
                $fieldErrors = $this->validateFieldType($name, $value, $paramSchema);
                foreach ($fieldErrors as $fieldError) {
                    $errors[$name][] = $fieldError;
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $fieldSchema
     *
     * @return list<string>
     */
    private function validateFieldType(string $field, mixed $value, array $fieldSchema): array
    {
        $errors = [];

        /** @var mixed $type */
        $type = $fieldSchema['type'] ?? null;

        if (!is_string($type)) {
            return $errors;
        }

        $typeError = $this->checkType($field, $value, $type);
        if ($typeError !== null) {
            $errors[] = $typeError;
        }

        /** @var mixed $format */
        $format = $fieldSchema['format'] ?? null;

        if (is_string($format) && is_string($value)) {
            $formatError = $this->checkFormat($field, $value, $format);
            if ($formatError !== null) {
                $errors[] = $formatError;
            }
        }

        /** @var mixed $minLength */
        $minLength = $fieldSchema['minLength'] ?? null;

        if (is_int($minLength) && is_string($value) && mb_strlen($value) < $minLength) {
            $errors[] = sprintf('The %s field must be at least %d characters', $field, $minLength);
        }

        /** @var mixed $maxLength */
        $maxLength = $fieldSchema['maxLength'] ?? null;

        if (is_int($maxLength) && is_string($value) && mb_strlen($value) > $maxLength) {
            $errors[] = sprintf('The %s field must be at most %d characters', $field, $maxLength);
        }

        /** @var mixed $minimum */
        $minimum = $fieldSchema['minimum'] ?? null;

        if (is_numeric($minimum) && is_numeric($value) && (float) $value < (float) $minimum) {
            $errors[] = sprintf('The %s field must be at least %s', $field, (string) $minimum);
        }

        /** @var mixed $maximum */
        $maximum = $fieldSchema['maximum'] ?? null;

        if (is_numeric($maximum) && is_numeric($value) && (float) $value > (float) $maximum) {
            $errors[] = sprintf('The %s field must be at most %s', $field, (string) $maximum);
        }

        /** @var mixed $enum */
        $enum = $fieldSchema['enum'] ?? null;

        if (is_array($enum) && !in_array($value, $enum, true)) {
            $errors[] = sprintf(
                'The %s field must be one of: %s',
                $field,
                implode(', ', array_map(static fn(mixed $v): string => (string) $v, $enum)),
            );
        }

        return $errors;
    }

    private function checkType(string $field, mixed $value, string $type): ?string
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'number' => is_numeric($value),
            'boolean' => is_bool($value) || $value === '0' || $value === '1' || $value === 'true' || $value === 'false',
            'array' => is_array($value),
            'object' => is_array($value),
            default => true,
        };

        if (!$valid) {
            return sprintf('The %s field must be of type %s', $field, $type);
        }

        return null;
    }

    private function checkFormat(string $field, string $value, string $format): ?string
    {
        $valid = match ($format) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'uuid' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1,
            'date' => $this->isValidDate($value),
            'date-time' => $this->isValidDateTime($value),
            'uri', 'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            default => true,
        };

        if (!$valid) {
            return sprintf('The %s field must be a valid %s', $field, $format);
        }

        return null;
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function isValidDateTime(string $value): bool
    {
        try {
            new \DateTimeImmutable($value);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $operation
     *
     * @return array<string, mixed>|null Schema with 'required' and 'properties' keys, or null if no body schema
     */
    private function extractBodySchema(array $operation): ?array
    {
        /** @var mixed $requestBody */
        $requestBody = $operation['requestBody'] ?? null;

        if (!is_array($requestBody)) {
            return null;
        }

        /** @var mixed $content */
        $content = $requestBody['content'] ?? null;

        if (!is_array($content)) {
            return null;
        }

        /** @var mixed $jsonContent */
        $jsonContent = $content['application/json'] ?? null;

        if (!is_array($jsonContent)) {
            return null;
        }

        /** @var mixed $schema */
        $schema = $jsonContent['schema'] ?? null;

        if (!is_array($schema)) {
            return null;
        }

        /** @var array<string, mixed> $schema */
        return $schema;
    }
}
