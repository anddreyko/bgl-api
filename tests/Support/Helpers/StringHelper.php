<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Helpers;

final class StringHelper
{
    /**
     * Проверка выводимости значения как строки.
     *
     * @param string|object $value
     *
     * @return bool
     */
    public static function isString($value): bool
    {
        return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
    }

    /**
     * Замена плейсхолдеров в тексте на указанные значения в массиве.
     *
     * Примеры:
     *  replacePlaceholders('User {user} created', ['user' => 'John Doe'])              => 'User John Doe created'
     *  replacePlaceholders('User #user# created', ['user' => 'Anonymous', '#', '#'])   => 'User Anonymous created'
     *
     * @param T $content
     * @param array $placeholders
     * @param string $prefix
     * @param string $postfix
     *
     * @template T of string|\Stringable
     * @return T
     */
    public static function placeholders(
        $content,
        array $placeholders,
        string $prefix = '{',
        string $postfix = '}'
    ) {
        if (empty($placeholders) || !self::isString($content)) {
            return $content;
        }

        $replacements = [];
        foreach ($placeholders as $name => $value) {
            if (is_scalar($value)) {
                $replacements[$prefix . $name . $postfix] = self::forceToString($value);
            } elseif (is_array($value)) {
                $replacements[$prefix . $name . $postfix] = '[Array]';
            } elseif ($value instanceof \Stringable) {
                $replacements[$prefix . $name . $postfix] = self::forceToString($value);
            } elseif (is_object($value)) {
                $replacements[$prefix . $name . $postfix] = '[Object]';
            } elseif ($value === null) {
                $replacements[$prefix . $name . $postfix] = '[Null]';
            }
        }

        return strtr($content, $replacements);
    }

    /**
     * Принудительная конвертация в строку, с потерей данных для отображения.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function forceToString($value): string
    {
        if (!self::isString($value)) {
            if ($value === null) {
                $value = 'NULL';
            } elseif ($value === true) {
                $value = 'TRUE';
            } elseif ($value === false) {
                $value = 'FALSE';
            } elseif (is_float($value)) {
                $value = str_replace(',', '.', (string)$value);
            } elseif (is_array($value)) {
                $value = var_export(
                    array_map(
                        static fn($item) => self::forceToString($item),
                        $value
                    ),
                    true
                );
            } elseif (is_object($value)) {
                try {
                    $valueReflection = new \ReflectionClass($value);
                    if ($valueReflection->isAnonymous()) {
                        $value = 'OBJECT OF ANONYMOUS CLASS';
                    } else {
                        $value = var_export($value, true);
                    }
                } catch (\Exception $e) {
                    try {
                        $value = serialize($value);
                    } catch (\Exception $e) {
                        $value = 'OBJECT';
                    }
                }
            }
        }

        return (string)$value;
    }
}
