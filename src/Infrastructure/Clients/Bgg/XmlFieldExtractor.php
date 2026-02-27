<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Clients\Bgg;

use Bgl\Core\Serialization\Denormalizer;

final readonly class XmlFieldExtractor implements Denormalizer
{
    #[\Override]
    public function denormalize(mixed $source, array $mapping, array $required = []): ?array
    {
        if (!$source instanceof \SimpleXMLElement) {
            throw new \InvalidArgumentException(
                sprintf('Expected SimpleXMLElement, got %s', get_debug_type($source)),
            );
        }

        return $this->extract($source, $mapping, $required);
    }

    /**
     * @param array<string, string> $mapping  XML path => field name
     * @param list<string> $required          Required field names
     *
     * @return array<string, string|int|null>|null  null if required fields are missing
     */
    public function extract(\SimpleXMLElement $item, array $mapping, array $required = []): ?array
    {
        $result = [];
        foreach ($mapping as $xmlPath => $fieldName) {
            $result[$fieldName] = $this->resolveXmlPath($item, $xmlPath);
        }

        foreach ($required as $field) {
            $value = $result[$field] ?? null;
            if ($value === null || $value === '' || $value === 0) {
                return null;
            }
        }

        return $result;
    }

    private function resolveXmlPath(\SimpleXMLElement $item, string $path): string|int|null
    {
        // @attr -- attribute of current element (e.g. @id)
        if (str_starts_with($path, '@')) {
            $attr = substr($path, 1);
            $value = (string)($item[$attr] ?? '');

            return $value !== '' ? $this->castNumeric($value) : null;
        }

        // child@attr -- attribute of child element (e.g. name@value)
        if (str_contains($path, '@')) {
            $parts = explode('@', $path, 2);
            $child = $parts[0];
            $attr = $parts[1] ?? '';
            if ($attr === '') {
                return null;
            }
            /** @var \SimpleXMLElement|null $childElement */
            $childElement = $item->{$child};
            if ($childElement === null || !isset($childElement[$attr])) {
                return null;
            }
            $value = (string)$childElement[$attr];

            return $value !== '' ? $this->castNumeric($value) : null;
        }

        // plain child element text
        $value = (string)($item->{$path} ?? '');

        return $value !== '' ? $this->castNumeric($value) : null;
    }

    private function castNumeric(string $value): string|int
    {
        return ctype_digit($value) ? (int)$value : $value;
    }
}
