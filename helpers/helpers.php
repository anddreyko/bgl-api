<?php

declare(strict_types=1);

use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param int|string|bool|null $default
     *
     * @return int|string|bool|null
     */
    function env(string $key, mixed $default = null): mixed
    {
        static $variables;

        if ($variables === null) {
            $variables = (new DotenvFactory([new EnvConstAdapter(), new PutenvAdapter(), new ServerConstAdapter()]))
                ->createImmutable();
        }

        $value = $variables->get($key);

        if ($value === null) {
            return $default;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $value
            };
        }

        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
