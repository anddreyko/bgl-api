<?php

declare(strict_types=1);

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\ServerRequestValidator as LeagueServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Container\ContainerInterface;

return [
    OpenApi::class => static function (ContainerInterface $container): OpenApi {
        /** @var array<string, mixed> $config */
        $config = $container->get('openapi');

        $config['openapi'] = '3.0.0';

        /** @var array<string, array<string, mixed>> $paths */
        $paths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];

        // Ensure each operation has a minimal 'responses' block (required by OpenAPI 3.0)
        foreach ($paths as $pathKey => $methods) {
            /** @var mixed $operation */
            foreach ($methods as $method => $operation) {
                if (!is_array($operation)) {
                    continue;
                }

                /** @var array<string, mixed> $operation */
                if (!isset($operation['responses'])) {
                    $operation['responses'] = [
                        '200' => ['description' => 'Success'],
                    ];
                }

                $paths[$pathKey][$method] = $operation;
            }
        }

        $config['paths'] = $paths;

        return new OpenApi($config);
    },
    LeagueServerRequestValidator::class => static function (
        ContainerInterface $container,
    ): LeagueServerRequestValidator {
        /** @var OpenApi $schema */
        $schema = $container->get(OpenApi::class);

        return new ValidatorBuilder()->fromSchema($schema)->getServerRequestValidator();
    },
];
