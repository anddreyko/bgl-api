<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\ValueObjects;

use App\Infrastructure\Http\ValueObjects\Url;
use Codeception\Test\Unit;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @covers \App\Infrastructure\Http\ValueObjects\Url
 */
class UrlTest extends Unit
{
    /**
     * Проверка полей тестируемого объекта после преобразования URL.
     *
     * @param string $url
     * @param string $field
     * @param string|null $expected
     *
     * @throws ExpectationFailedException
     * @throws \InvalidArgumentException
     * @dataProvider getCasesFromString
     */
    public function testConvertFromString(string $url, string $field, ?string $expected): void
    {
        $urlHelper = Url::convertFromString($url);
        self::assertEquals($expected, $urlHelper->$field);
    }

    /**
     * Данные для проверки полей тестируемого объекта после преобразования URL.
     *
     * @return array<array{ url: mixed, field: mixed, expected: mixed }>
     */
    public function getCasesFromString(): array
    {
        return [
            [
                'url' => 'http://4records.bg',
                'field' => 'scheme',
                'expected' => 'http',
            ],
            [
                'url' => 'https://4records.bg',
                'field' => 'scheme',
                'expected' => 'https',
            ],
            [
                'url' => '4rec://4records.bg',
                'field' => 'scheme',
                'expected' => '4rec',
            ],
            [
                'url' => '4records.bg',
                'field' => 'scheme',
                'expected' => null,
            ],
            [
                'url' => 'http://4records.bg',
                'field' => 'host',
                'expected' => '4records.bg',
            ],
            [
                'url' => '://4records.bg',
                'field' => 'host',
                'expected' => null,
            ],
            [
                'url' => 'http://4records.bg:8080',
                'field' => 'port',
                'expected' => '8080',
            ],
            [
                'url' => '://4records.bg:8080',
                'field' => 'port',
                'expected' => null,
            ],
            [
                'url' => 'http://4records.bg/',
                'field' => 'path',
                'expected' => '/',
            ],
            [
                'url' => 'http://4records.bg/product/123456',
                'field' => 'path',
                'expected' => '/product/123456',
            ],
            [
                'url' => '://4records.bg/',
                'field' => 'path',
                'expected' => '://4records.bg/',
            ],
            [
                'url' => 'http://4records.bg/?params=y&cache=false',
                'field' => 'query',
                'expected' => 'params=y&cache=false',
            ],
            [
                'url' => 'http://4records.bg/#fragment',
                'field' => 'fragment',
                'expected' => 'fragment',
            ],
            [
                'url' => 'http://ivanov:qwerty@4records.bg/#fragment',
                'field' => 'user',
                'expected' => 'ivanov',
            ],
            [
                'url' => 'http://ivanov:qwerty@4records.bg',
                'field' => 'pass',
                'expected' => 'qwerty',
            ],
            [
                'url' => 'http://ivan@4records.bg',
                'field' => 'user',
                'expected' => 'ivan',
            ],
            [
                'url' => 'http://qwerty@4records.bg',
                'field' => 'pass',
                'expected' => null,
            ],
        ];
    }

    /**
     * Проверка метода получения URL из тестируемого объекта.
     *
     * @param array $options
     * @param string|null $expected
     *
     * @throws ExpectationFailedException
     * @throws \InvalidArgumentException
     * @dataProvider getCasesFromArray
     */
    public function testGetUrl(array $options, ?string $expected): void
    {
        $urlHelper = Url::convertFromArray($options);
        self::assertEquals($expected, $urlHelper->getUrl());
    }

    /**
     * Данные для проверки метода получения URL из тестируемого объекта.
     *
     * @return array<array{ options: mixed, expected: mixed }>
     */
    public function getCasesFromArray(): array
    {
        return array_merge(
            [
                [
                    'options' => [
                        'scheme' => 'https',
                        'host' => '4records.bg',
                    ],
                    'expected' => 'https://4records.bg',
                ],
                [
                    'options' => [
                        'scheme' => '',
                        'host' => '4records.bg',
                        'path' => '/',
                    ],
                    'expected' => '://4records.bg/',
                ],
                [
                    'options' => [
                        'scheme' => '4rec',
                        'path' => 'special-offers',
                    ],
                    'expected' => '4rec://special-offers',
                ],
                [
                    'options' => [
                        'scheme' => '4rec',
                        'path' => '/catalog-apple',
                    ],
                    'expected' => '4rec://catalog-apple',
                ],
                [
                    'options' => [
                        'path' => 'path',
                        'query' => [
                            'param_1' => 42,
                            'param_2' => 'test',
                        ],
                    ],
                    'expected' => '/path?param_1=42&param_2=test',
                ],
            ],
            $this->getCasesRelativeUrls()
        );
    }

    /**
     * Данные для проверки метода получения относительного URL из тестируемого объекта.
     *
     * @return array<array{ options: mixed, expected: mixed }>
     */
    public function getCasesRelativeUrls(): array
    {
        return [
            [
                'options' => [],
                'expected' => null,
            ],
            [
                'options' => [
                    'path' => 'product/111111',
                ],
                'expected' => '/product/111111',
            ],
            [
                'options' => [
                    'path' => '/product/222222',
                ],
                'expected' => '/product/222222',
            ],
        ];
    }

    /**
     * Проверка метода получения относительного URL из тестируемого объекта.
     *
     * @param array $options
     * @param string|null $expected
     *
     * @throws ExpectationFailedException
     * @throws \InvalidArgumentException
     * @dataProvider getCasesRelativeUrls
     */
    public function testGetRelativeUrl(array $options, ?string $expected): void
    {
        $urlHelper = Url::convertFromArray($options);
        self::assertEquals($expected, $urlHelper->getRelativeUrl());
    }

    /**
     * Проверка метода получения массива GET-параметров из URL.
     *
     * @param string $url
     * @param array $expected
     *
     * @dataProvider getCasesParsedQueryParameters
     */
    public function testGetParsedQuery(string $url, array $expected): void
    {
        $query = Url::convertFromString($url)->getParsedQuery();
        self::assertEquals($expected, $query);
    }

    /**
     * Данные для проверки метода получения массива GET-параметров из URL.
     *
     * @return array
     */
    public function getCasesParsedQueryParameters(): array
    {
        return [
            [
                'url' => '?query-1=1&query-2=2',
                'expected' => ['query-1' => 1, 'query-2' => '2'],
            ],
            [
                'url' => '/',
                'expected' => [],
            ],
            [
                'url' => '?',
                'expected' => [],
            ],
        ];
    }

    /**
     * Проверка метода получения значения GET-параметра из URL по ключу.
     *
     * @param string $key
     * @param string $url
     * @param string|null $expected
     *
     * @dataProvider getCasesQueryParameters
     */
    public function testGetQueryParam(string $key, string $url, ?string $expected): void
    {
        $queryParam = Url::convertFromString($url)->getQueryParam($key);
        self::assertEquals($expected, $queryParam);
    }

    /**
     * Данные для проверки метода получения значения GET-параметра из URL по ключу.
     *
     * @return array
     */
    public function getCasesQueryParameters(): array
    {
        return [
            [
                'key' => 'query-1',
                'url' => '?',
                'expected' => null,
            ],
            [
                'key' => 'query-1',
                'url' => '?query-1=test',
                'expected' => 'test',
            ],
            [
                'key' => 'query-1',
                'url' => '?query-1',
                'expected' => null,
            ],
            [
                'key' => '',
                'url' => '?query-1=test',
                'expected' => null,
            ],
        ];
    }

    public function testConvertNotExistedProperties(): void
    {
        $property = 'not_exist_prop';
        $url = (new Url())->convert([$property => 'not-value']);

        $this->assertFalse(property_exists($url, $property));
    }
}
